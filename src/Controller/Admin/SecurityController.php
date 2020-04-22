<?php

namespace App\Controller\Admin;

use Psr\Log\LoggerInterface;
use App\Entity\User\AdminUser;
use App\Form\Admin\FirstLoginType;
use App\Form\Admin\NewPasswordType;
use App\Form\Admin\TokenPasswordType;
use App\Form\Admin\ForgotPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\RandomStringGeneratorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
     const TOKEN_LENGTH = 10;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RandomStringGeneratorService
     */
    private $randomStringGenerator;

    /**
     * SecurityController constructor.
     * @param EntityManagerInterface $entityManager
     * @param RandomStringGeneratorService $generatorService
     */
    public function __construct(EntityManagerInterface $entityManager, RandomStringGeneratorService $generatorService)
    {
        $this->entityManager = $entityManager;
        $this->randomStringGenerator = $generatorService;
    }


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
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function changePassword(Request $request, TokenStorageInterface $tokenStorage, LoggerInterface $logger, TranslatorInterface $translator): Response
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
                $this->entityManager->flush();
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
     * Forgot password view.
     * @Route("/forgot-password", name="app_forgot_password")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param SenderInterface $sender
     * @return Response
     */
    public function forgotPassword(Request $request, TranslatorInterface $translator, SenderInterface $sender)
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $user = $this->entityManager->getRepository('App:User\AdminUser')->findOneBy(['email' => $email]);

            if (!$user instanceof AdminUser) {
                $this->addFlash('danger', $translator->trans('app.ui.forgot_password.not_found_error'));

                return $this->redirectToRoute('app_forgot_password');
            }

            $token = $this->generateToken();
            $this->sendTokenEmail($user, $token, $sender);

            $user->setPasswordRequestedAt(new \DateTime());
            $user->setPasswordResetToken($token);
            $user->setPasswordRecoveryId(md5($token));

            $this->entityManager->flush();

            return $this->redirectToRoute('app_validate_token', ['user' => $email]);
        }

        return $this->render('/admin/security/forgot-password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Validate token view.
     * @Route("/validate-token", name="app_validate_token")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function validateToken(Request $request, TranslatorInterface $translator)
    {
        $form = $this->createForm(TokenPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $form->get('token')->getData();

            /** @var AdminUser|null $user */
            $user = $this->entityManager->getRepository('App:User\AdminUser')->findOneBy(['passwordResetToken' => $token]);

            if ($user instanceof AdminUser) {
                return $this->redirectToRoute('app_new_password', ['passwordRecoveryId' => md5($token)]);
            }

            $this->addFlash('danger', $translator->trans('app.ui.validate_token.user_not_found'));

            return $this->redirectToRoute('app_validate_token');
        }

        return $this->render('/admin/security/validate-token.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Create a new password
     * @Route("/{passwordRecoveryId}/new-password", name="app_new_password")
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function newPassword(Request $request, TranslatorInterface $translator)
    {
        $passwordRecoveryId = $request->get('passwordRecoveryId');

        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        /** @var AdminUser|null $user */
        $user = $this->entityManager->getRepository('App:User\AdminUser')->findOneBy(['passwordRecoveryId' => $passwordRecoveryId]);

        if (!$user instanceof AdminUser) {
            return $this->redirectToRoute('dashboard_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            $user->setPlainPassword($password);
            $user->setPasswordResetToken(null);
            $user->setPasswordRequestedAt(null);
            $user->setPasswordRecoveryId(null);

            $this->addFlash('success', $translator->trans('app.ui.forgot_password.password_updated'));

            $this->entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('/admin/security/new-password.html.twig', [
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

    /**
     * Generate a unique password reset token.
     * @return string
     */
    private function generateToken()
    {
        $token = $this->randomStringGenerator->generate(self::TOKEN_LENGTH);

        /** @var AdminUser|null */
        $user = $this->entityManager->getRepository('App:User\AdminUser')
            ->findOneBy(['passwordResetToken' => $token]);

        if (!$user instanceof AdminUser) {
            return $token;
        } else {
            return $this->generateToken();
        }
    }

    /**
     * @param AdminUser $user
     * @param string $token
     * @param SenderInterface $sender
     */
    private function sendTokenEmail(AdminUser $user, string $token, SenderInterface $sender)
    {
        $sender->send('admin_forgot_password_request_token', [
            $user->getEmail()
        ], [
            'name' => $user->getFullName(),
            'email' => $user->getEmail(),
            "code" => $token,
        ]);
    }
}
