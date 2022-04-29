<?php

namespace App\Controller\ShopApi;

use Psr\Log\LoggerInterface;
use App\Form\ShopApi\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\UserBundle\UserEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\GenericEvent;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * RegisterController
 * @Route("/me")
 */
class ChangePasswordController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * SecurityController constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Complete register
     * @Route("/change-password", name="shop_api_change_password", methods={"PUT"})
     * @param Request $request
     * @return Response
     */
    public function changePasswordAction(Request $request)
    {
        $shopUser = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $shopUser);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $shopUser->setPlainPassword($newPassword);

            try {
                $this->handleChangePassword($shopUser, $newPassword);

                return new JsonResponse(['Nueva contraseña cambiada'], Response::HTTP_NO_CONTENT);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());

                return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
            }
        }

        return new JsonResponse(['message' => 'No se pudo cambiar la contraseña.', 'errors' => $this->getErrorsFromForm($form)], Response::HTTP_FORBIDDEN);
    }

    /**
     * @param UserInterface|null $user
     * @param $newPassword
     */
    private function handleChangePassword(?UserInterface $user, $newPassword)
    {
        $user->setPlainPassword($newPassword);

        $this->dispatcher->dispatch(UserEvents::PRE_PASSWORD_CHANGE, new GenericEvent($user));
        $this->entityManager->flush();
        $this->dispatcher->dispatch(UserEvents::POST_PASSWORD_CHANGE, new GenericEvent($user));
    }

    /**
     * @param FormInterface $form
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }
}
