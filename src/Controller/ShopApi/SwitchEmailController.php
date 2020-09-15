<?php

namespace App\Controller\ShopApi;

use App\Entity\User\ShopUser;
use App\Model\APIResponse;
use App\Service\RandomStringGeneratorService;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SwitchEmailController
 * @Route("/email")
 */
class SwitchEmailController extends AbstractFOSRestController
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RandomStringGeneratorService
     */
    private $generatorService;

    /**
     * SwitchEmailController constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @param RandomStringGeneratorService $generatorService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        RandomStringGeneratorService $generatorService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->generatorService = $generatorService;
    }

    /**
     * @Route(
     *     "/switch-customer-email.{_format}",
     *     name="shop_api_change_email_request",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @param SenderInterface $sender
     * @return Response
     */
    public function newAction(Request $request, SenderInterface $sender): Response
    {
        /** @var ShopUser $user */
        $user = $this->getUser();
        $old = $request->get('email');
        $new = $request->get('new_email');

        $statusCode = Response::HTTP_BAD_REQUEST;

        if (!$user instanceof ShopUser) {
            $statusCode = Response::HTTP_FORBIDDEN;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.switch_email.user_not_found'));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        /** Validate empty data */
        if (empty($old) || empty($new)) {
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.switch_email.enter_email'));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        /** Validate data type */
        if (!filter_var($old, FILTER_VALIDATE_EMAIL) || !filter_var($new, FILTER_VALIDATE_EMAIL)) {
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.switch_email.invalid_email_formats'));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        /** Validate new email is different than current */
        if ($user->getEmail() == $new) {
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.switch_email.new_email_is_same_to_current'));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        /** Validate current email */
        if ($user->getEmail() != $old) {
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.switch_email.invalid_current_email'));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        /** Validate email is not in use */
        $exists = $this->entityManager->getRepository('App:User\ShopUser')
            ->findOneBy(['username' => $new]);

        if ($exists instanceof ShopUser) {
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.switch_email.email_already_in_use'));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $sender->send('change_email_verification', [
            $new
        ], [
            'user' => $user,
            'token' => $this->generateRandomToken($user, $new),
        ]);

        $statusCode = Response::HTTP_OK;
        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, $this->translator->trans('app.api.switch_email.success_message'));
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @param ShopUser $user
     * @param $newEmail
     * @return string
     */
    private function generateRandomToken(ShopUser $user, $newEmail): string
    {
        $token = $this->generatorService->generate(24);

        $user->setEmailVerificationToken($token);
        $user->setTempEmail($newEmail);

        $this->entityManager->flush();

        return $token;
    }
}
