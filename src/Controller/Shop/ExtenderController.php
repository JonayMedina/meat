<?php

namespace App\Controller\Shop;

use App\Entity\User\ShopUser;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use App\Form\Shop\ChangeEmailType;
use App\Form\Shop\BillingProfileType;
use App\Repository\FavoriteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Sylius\Component\Core\Repository\AddressRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sylius\Component\Resource\Generator\RandomnessGeneratorInterface;

class ExtenderController extends AbstractController
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * ExtenderController constructor.
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/account/favorites", name="user_favorites")
     * @param FavoriteRepository $favoriteRepository
     * @return Response
     */
    public function favoritesAction(FavoriteRepository $favoriteRepository)
    {
        /**
         * @var ShopUser $user
         */
        $user = $this->getUser();

        return $this->render('shop/account/favorites.html.twig', ['favorites' => $favoriteRepository->findBy(['shopUser' => $user])]);
    }

    /**
     * @Route("/account/changed-password", name="user_changed_password")
     * @param Request $request
     * @return Response
     */
    public function changedPassword(Request $request) {
        return $this->redirectToRoute('sylius_shop_account_change_password', ['success' => true]);
    }

    /**
     * @Route("/account/billing", name="user_billing")
     * @param Request $request
     * @param AddressRepositoryInterface $addressRepository
     * @return Response
     */
    public function updateBillingAction(Request $request, AddressRepositoryInterface $addressRepository) {
        /** @var ShopUser $user */
        $user = $this->getUser();
        /** @var Customer $customer */
        $customer = $user->getCustomer();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(BillingProfileType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $customer->getDefaultBillingAddress();

            if ($address instanceof Address) {
                $address->setCustomer($customer);
                $address->setType(Address::TYPE_BILLING);
                $customer->setDefaultBillingAddress($address);
            }

            $em->flush();

            return $this->redirectToRoute('user_billing', ['success' => true]);
        }

        return $this->render('shop/account/updateBilling.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/account/change-email", name="user_change_email")
     * @param Request $request
     * @param RandomnessGeneratorInterface $generator
     * @param SenderInterface $sender
     * @return Response
     */
    public function changeEmailAction(Request $request, RandomnessGeneratorInterface $generator, SenderInterface $sender) {
        $errors = [];
        $em = $this->getDoctrine()->getManager();
        /** @var ShopUser $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangeEmailType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->validateEmails($form->getData());

            if (count($errors) <= 0) {
                $emails = $form->getData();
                $user->setTempEmail($emails['newEmail']);
                $user->setEmailVerificationToken($generator->generateNumeric(10));

                $em->flush();

                $sender->send('change_email_verification', [$emails['email']], ['user' => $user]);

                return $this->redirectToRoute('user_change_email', ['success' => true]);
            }
        }

        return $this->render('shop/account/changeEmail.html.twig', ['user' => $user, 'form' => $form->createView(), 'errors' => $errors]);
    }

    /**
     * @param $emails
     * @return array
     */
    private function validateEmails($emails) {
        $errors = [];
        /** @var ShopUser $user */
        $user = $this->getUser();

        $existUser = $this->userRepository->findOneBy(['username' => $emails['newEmail']]);

        if ($emails['email'] != $user->getEmail()) {
            $errors['email'] = 'app.ui.change_email.error.incorrect_actual_email';
        } else if ($emails['email'] == $emails['newEmail']) {
            $errors['email'] = 'app.ui.change_email.error.same_email';
        } else if ($existUser instanceof ShopUser) {
            $errors['email'] = 'app.ui.change_email.error.email_is_used';
        }

        return $errors;
    }
}
