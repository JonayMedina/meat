<?php

namespace App\Controller\Admin;

use App\Entity\User\AdminUser;
use App\Form\Admin\FirstLoginType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('/admin/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Update password view.
     * @Route("/update-password", name="app_update_password")
     * @param Request $request
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function changePassword(Request $request, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, LoggerInterface $logger, TranslatorInterface $translator): Response
    {
        /** @var AdminUser $user */
        $user = $tokenStorage->getToken()->getUser();
        $form = $this->createForm(FirstLoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            $user->setPlainPassword($password);
            $user->setLastPasswordUpdate(new \DateTime());

            try {
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('app.ui.first_password.success'));

                return $this->redirectToRoute('dashboard_index');
            } catch (\Exception $exception) {
                $logger->error($exception->getMessage());
                $this->addFlash('success', $translator->trans('app.ui.first_password.error'));

                return $this->redirectToRoute('app_update_password');
            }
        }

        return $this->render('/admin/security/change-password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
