<?php

namespace App\Controller\Shop;

use DateTime;
use Exception;
use SM\SMException;
use App\Entity\Order\Order;
use Webmozart\Assert\Assert;
use App\Entity\User\ShopUser;
use App\Entity\User\UserOAuth;
use App\Entity\Customer\Customer;
use App\Form\Shop\AddPasswordType;
use App\Form\Shop\ChangeEmailType;
use App\Entity\Addressing\Address;
use App\Form\Shop\BillingProfileType;
use App\Repository\FavoriteRepository;
use App\Service\PaymentGatewayService;
use App\Form\Shop\DisconnectFacebookType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Core\OrderCheckoutStates;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\ShopApi\OAuthLoginController;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\User\Security\PasswordUpdater;
use Sylius\Component\User\Model\UserOAuthInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sylius\Component\User\Security\UserPbkdf2PasswordEncoder;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\User\Canonicalizer\CanonicalizerInterface;
use Sylius\Bundle\CustomerBundle\Form\Type\CustomerProfileType;
use Sylius\Component\Core\Repository\AddressRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Sylius\Component\Resource\Generator\RandomnessGeneratorInterface;
use Sylius\Component\Core\Model\ShopUserInterface as SyliusUserInterface;
use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerRegistrationType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Sylius\Component\Core\OrderPaymentStates;

class ExtenderController extends AbstractController
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var FactoryInterface */
    private $customerFactory;

    /** @var FactoryInterface */
    private $userFactory;

    /** @var CanonicalizerInterface */
    private $canonicalizer;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var FactoryInterface */
    private $oauthFactory;

    /** @var string */
    protected $paymentState = OrderPaymentStates::STATE_CART;

    /**
     * ExtenderController constructor.
     * @param UserRepositoryInterface $userRepository
     * @param FactoryInterface $customerFactory
     * @param FactoryInterface $userFactory
     * @param CanonicalizerInterface $canonicalizer
     * @param CustomerRepositoryInterface $customerRepository
     * @param FactoryInterface $oauthFactory
     */
    public function __construct(UserRepositoryInterface $userRepository, FactoryInterface $customerFactory, FactoryInterface $userFactory, CanonicalizerInterface $canonicalizer, CustomerRepositoryInterface $customerRepository, FactoryInterface $oauthFactory) {
        $this->userRepository = $userRepository;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        $this->canonicalizer = $canonicalizer;
        $this->customerRepository = $customerRepository;
        $this->oauthFactory = $oauthFactory;
    }

    /**
     * @Route("/account/favorites", name="user_favorites")
     * @param FavoriteRepository $favoriteRepository
     * @return Response
     */
    public function favoritesAction(FavoriteRepository $favoriteRepository) {
        /** @var ShopUser $user */
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

                $sender->send('change_email_verification', [$emails['newEmail']], ['user' => $user]);

                return $this->redirectToRoute('user_change_email', ['success' => true]);
            }
        }

        return $this->render('shop/account/changeEmail.html.twig', ['user' => $user, 'form' => $form->createView(), 'errors' => $errors]);
    }

    /**
     * @param Request $request
     * @param AddressRepositoryInterface $addressRepository
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function updateCustomerAction(Request $request, AddressRepositoryInterface $addressRepository) {
        $this->get('session')->getFlashBag()->clear();
        /** @var ShopUser $user */
        $user = $this->getUser();
        /** @var Customer $customer */
        $customer = $user->getCustomer();
        $em = $this->getDoctrine()->getManager();
        $addresses = [];

        if ($request->request->get('sylius_customer_profile')) {
            $profile = $request->request->get('sylius_customer_profile');
            if ($profile['birthday']) {
                $formatted = DateTime::createFromFormat('d/m/Y', $profile['birthday']);
                $date = $formatted->format('Y-m-d');

                $profile['birthday'] = New DateTime($date);
            }

            $request->request->set('sylius_customer_profile', $profile);

            for ($i=0; $i<ShopUser::SHIPPING_ADDRESS_LIMIT; $i++) {
                if (isset($profile['address_' . $i])) {
                    $address = $profile['address_' . $i];

                    if (!$address['id']) {
                        if ($address['fullAddress'] && $address['annotations'] && $address['phoneNumber']) {
                            $addresses[] = $address;
                        }
                    }
                }
            }
        }

        $form = $this->createForm(CustomerProfileType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profile = $request->request->get('sylius_customer_profile');

            for ($i=0; $i<ShopUser::SHIPPING_ADDRESS_LIMIT; $i++) {
                if (isset($profile['address_' . $i])) {
                    $address = $profile['address_' . $i];

                    if (!$address['id']) {
                        if ($address['fullAddress'] && $address['annotations'] && $address['phoneNumber']) {
                            $addressN = new Address();
                            $addressN->setFullAddress($address['fullAddress']);
                            $addressN->setAnnotations($address['annotations']);
                            $addressN->setPhoneNumber($address['phoneNumber']);
                            $addressN->setType(Address::TYPE_SHIPPING);
                            $addressN->setCustomer($customer);

                            $em->persist($addressN);

                            if ($address['default'] == 'true') {
                                $em->flush();
                                $customer->setDefaultAddress($addressN);
                            }
                        }
                    } else {
                        /** @var Address $addressE */
                        $addressE = $addressRepository->find($address['id']);

                        $addressE->setFullAddress($address['fullAddress']);
                        $addressE->setAnnotations($address['annotations']);
                        $addressE->setPhoneNumber($address['phoneNumber']);

                        if ($address['default'] == 'true') {
                            $customer->setDefaultAddress($addressE);
                        }
                    }
                }
            }

            $em->flush();

            return $this->redirectToRoute('sylius_shop_account_dashboard');
        }

        return $this->render('@SyliusShop/Account/profileUpdate.html.twig', ['customer' => $customer,'form' => $form->createView(), 'nonAddresses' => $addresses]);
    }

    /**
     * @param Request $request
     * @param RedirectController $redirectController
     * @return Response
     */
    public function redirectToCheckoutAddress(Request $request, RedirectController $redirectController) {
        $this->get('session')->getFlashBag()->clear();
        $request->getSession()->set('payment', null);
        $request->getSession()->set('card', null);

        return $redirectController->redirectAction($request, $request->attributes->get('route'));
    }

    /**
     * @param Request $request
     * @param string $tokenValue
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentGatewayService $paymentGateway
     * @param SenderInterface $sender
     * @return RedirectResponse
     * @throws SMException
     */
    public function payOrderAction(Request $request, $tokenValue, OrderRepositoryInterface $orderRepository, PaymentGatewayService $paymentGateway, SenderInterface $sender) {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $payment = $session->get('payment');
        $card = $session->get('card');
        $order = $orderRepository->findOneBy(['tokenValue' => $tokenValue]);

        if ($order instanceof Order) {
            $order->setState(Order::STATE_CART);

            if ($payment == 'card') {
                $expDate = explode("/", str_replace(" ", "", $card['expirationDate']));
                $formattedDate = $expDate[1].$expDate[0];
                $result = $paymentGateway->orderPayment($order, $card['name'], str_replace(" ", "", $card['number']), $formattedDate, $card['cvv']);

                $htmlForm = $result['HTMLFormData'];

                if ($htmlForm != '') {
                    
                    return $this->render('fac/three_ds.html.twig', [
                        'HTMLFormData' => $htmlForm,
                    ]);
                    
                } else {
                    $order->setCheckoutState(OrderCheckoutStates::STATE_PAYMENT_SELECTED);
                    $em->flush();

                    return $this->redirectToRoute('sylius_shop_checkout_complete', ['error' => $result['responseCode'] ?? 'Unknown']);
                }
            } else {
                $result = $paymentGateway->cashOnDelivery($order);

                if ($result['message']) {
                    $session->set('tokenValue', $order->getTokenValue());
                    $session->set('payment', null);
                    $session->set('card', null);
                    $sender->send('order_ticket', [$order->getCustomer()->getEmail()], ['order' => $order]);

                    return $this->redirectToRoute('sylius_shop_order_thank_you');
                } else {
                    $order->setCheckoutState(OrderCheckoutStates::STATE_PAYMENT_SELECTED);
                    $em->flush();

                    return $this->redirectToRoute('sylius_shop_checkout_complete', ['error' => true]);
                }
            }
        } else {
            return $this->redirectToRoute('sylius_shop_cart_summary', ['error' => true]);
        }
    }

     /**
     * @param Request $request
     * @return RedirectResponse
     * @param SenderInterface $sender
     */
    public function showAction(Request $request, SenderInterface $sender)
    {
        $id = $request->get('token');

        $manager = $this->get('doctrine')->getManager();
        
        $order = $manager->getRepository('App:Order\Order')->findOneBy(['tokenValue' => $id]);
        
        if($order->getPaymentState() != 'paid'){
            $order->setPaymentState(OrderPaymentStates::STATE_PAID);
            $manager->flush();
            $sender->send('order_ticket', [$order->getCustomer()->getEmail()], ['order' => $order]);
        }
        

        if ($order instanceof Order) {
            return $this->render('@SyliusShop/Order/thankYou.html.twig', ['order' => $order]);
        } else {
            return $this->redirectToRoute('sylius_shop_homepage');
        }
    }

    /**
     * @param Request $request
     * @param OrderRepositoryInterface $orderRepository
     * @return RedirectResponse|Response
     */
    public function thankYouAction(Request $request, OrderRepositoryInterface $orderRepository) {
        $session = $request->getSession();

        if ($session->get('tokenValue')) {
            $order = $orderRepository->findOneBy(['tokenValue' => $session->get('tokenValue')]);

            $em = $this->getDoctrine()->getManager();

            /** @var ShopUser $user */
            $user = $this->getUser();

            /** @var Customer $customer */
            $customer = $user->getCustomer();

            $trashShippingAddresses = $this->getDoctrine()->getRepository('App:Addressing\Address')->findBy(['customer' => $customer, 'parent' => $order->getShippingAddress()->getParent(), 'type' => Address::TYPE_SHIPPING]);

            /* Delete trash shipping address of customer profile */
            if (count($trashShippingAddresses) > 0) {
                foreach ($trashShippingAddresses as $trashAddress) {
                    if ($trashAddress instanceof Address) {
                        $em->remove($trashAddress);
                    }
                }

                $em->flush();
            }

            $trashBillingAddresses = $this->getDoctrine()->getRepository('App:Addressing\Address')->findBy(['customer' => $customer, 'type' => Address::TYPE_BILLING]);

            /* Delete trash billing address of customer profile */
            if (count($trashBillingAddresses) > 0) {
                foreach ($trashBillingAddresses as $trashAddress) {
                    if ($trashAddress instanceof Address) {
                        if ($trashAddress->getId() != $customer->getDefaultBillingAddress()->getId()) {
                            $em->remove($trashAddress);
                        }
                    }
                }

                $em->flush();
            }

            $session->set('tokenValue', null);

            if ($order instanceof Order) {
                return $this->render('@SyliusShop/Order/thankYou.html.twig', ['order' => $order]);
            } else {
                return $this->redirectToRoute('sylius_shop_homepage');
            }
        } else {
            return $this->redirectToRoute('sylius_shop_homepage');
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function disconnectFacebookAction(Request $request) {
        $this->get('session')->getFlashBag()->clear();
        /** @var ShopUser $user */
        $user = $this->getUser();
        /** @var Customer $customer */
        $customer = $user->getCustomer();
        $em = $this->getDoctrine()->getManager();
        $errors = [];

        $oauthUser = $this->container->get('doctrine')->getManager()->getRepository('App:User\UserOAuth')
            ->findOneBy(['provider' => OAuthLoginController::PROVIDER_FACEBOOK, 'user' => $user]);

        if ($request->getMethod() == 'POST') {
            if (!$oauthUser instanceof UserOAuth) {
                return $this->redirectToRoute('sylius_shop_account_dashboard');
            }
        }

        $form = $this->createForm(DisconnectFacebookType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->validateDisconnectData($form->getData());

            if (count($errors) <= 0) {
                $data = $form->getData();

                try {
                    $customer->setEmail($data['email']);
                    $customer->setEmailCanonical($data['email']);
                    $user->setEmail($data['email']);
                    $user->setEmailCanonical($data['email']);
                    $user->setPlainPassword($data['password']);

                    $passwordUpdater = new PasswordUpdater(new UserPbkdf2PasswordEncoder());
                    $passwordUpdater->updatePassword($user);

                    $em->remove($oauthUser);
                    $em->flush();

                    return $this->redirectToRoute('sylius_shop_account_disconnect_facebook', ['success' => true]);
                } catch (\Exception $e) {
                    return $this->redirectToRoute('sylius_shop_account_disconnect_facebook', ['error' => true]);
                }
            }
        }

        return $this->render('shop/account/disconnectFacebook.html.twig', ['form' => $form->createView(), 'errors' => $errors]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addPasswordAction(Request $request) {
        $this->get('session')->getFlashBag()->clear();
        /** @var ShopUser $user */
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $errors = [];

        $oauthUser = $this->container->get('doctrine')->getManager()->getRepository('App:User\UserOAuth')
            ->findOneBy(['provider' => OAuthLoginController::PROVIDER_FACEBOOK, 'user' => $user]);

        if ($request->getMethod() == 'POST') {
            if (!$oauthUser instanceof UserOAuth) {
                return $this->redirectToRoute('sylius_shop_account_dashboard');
            }
        }

        $form = $this->createForm(AddPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $this->validatePassword($form->getData());

            if (count($errors) <= 0) {
                $data = $form->getData();

                try {
                    $user->setPlainPassword($data['password']);

                    $passwordUpdater = new PasswordUpdater(new UserPbkdf2PasswordEncoder());
                    $passwordUpdater->updatePassword($user);

                    $em->flush();

                    return $this->redirectToRoute('user_change_email');
                } catch (\Exception $e) {
                    return $this->redirectToRoute('user_pre_change_email', ['error' => true]);
                }
            }
        }

        return $this->render('shop/account/preChangeEmail.html.twig', ['form' => $form->createView(), 'errors' => $errors]);
    }

    /**
     * @return Response
     */
    public function welcomeAction() {
        $this->get('session')->getFlashBag()->clear();

        return $this->render('/frontend/welcome.html.twig');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function grandCentralAction(Request $request) {
        $session = $request->getSession();
        $route = 'sylius_shop_homepage';
        $params = [];

        if ($session->get('redirect_to_route')) {
            $route = $session->get('redirect_to_route');

            if ($route == 'sylius_shop_account_dashboard') {
                if ($session->get('redirect_param')) {
                    $params = [$session->get('redirect_param') => true];

                    $session->remove('redirect_param');
                }

                if ($session->get('connected')) {
                    $params = ['connected' => true];
                }
            }

            $session->remove('redirect_to_route');
        }

        return $this->redirectToRoute($route, $params);
    }

    /**
     * @param Request $request
     * @return SyliusUserInterface|Response|UserInterface
     */
    public function oauthRegisterAction(Request $request) {
        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();
        $userData = [];

        if ($session->get('oauth_first_name') || $session->get('oauth_last_name') || $session->get('oauth_provider') || $session->get('oauth_identifier')) {
            $userData['first_name'] = $session->get('oauth_first_name');
            $userData['last_name'] = $session->get('oauth_last_name');
            $userData['provider'] = $session->get('oauth_provider');
            $userData['identifier'] = $session->get('oauth_identifier');
            $userData['access_token'] = $session->get('oauth_access_token');
            $userData['refresh_token'] = $session->get('oauth_identifier');
            $userData['pass'] = substr(sha1($session->get('oauth_identifier')), 0, 10);
        } else {
            return $this->redirectToRoute('sylius_shop_register');
        }

        $form = $this->createForm(CustomerRegistrationType::class, new Customer());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                /** @var SyliusUserInterface $user */
                $user = $this->userFactory->createNew();

                $canonicalEmail = $this->canonicalizer->canonicalize($data->getEmail());

                /** @var CustomerInterface $customer */
                $customer = $this->customerRepository->findOneBy(['emailCanonical' => $canonicalEmail]);

                if (null === $customer) {
                    /** @var CustomerInterface $customer */
                    $customer = $this->customerFactory->createNew();
                }

                $user->setCustomer($customer);

                // set default values taken from OAuth sign-in provider account
                if (null !== $email = $data->getEmail()) {
                    $customer->setEmail($email);
                }

                if (null !== $name = $data->getFirstName()) {
                    $customer->setFirstName($name);
                }

                if (null !== $lastName = $data->getLastName()) {
                    $customer->setLastName($lastName);
                }

                if (!$user->getUsername()) {
                    $user->setUsername($data->getEmail());
                }

                // set random password to prevent issue with not nullable field & potential security hole
                $user->setPlainPassword($userData['pass']);
                $user->setEnabled(true);

                $em->flush();

                $session->remove('oauth_first_name');
                $session->remove('oauth_last_name');
                $session->remove('oauth_provider');
                $session->remove('oauth_identifier');
                $session->remove('oauth_access_token');
                $session->remove('oauth_identifier');

                return $this->updateUserByOAuthUserResponse($user, $userData);
            } catch (\Exception $e) {
                return $this->redirectToRoute('oauth_register');
            }
        }

        return $this->render('/shop/oauthRegister.html.twig', ['form' => $form->createView(), 'oauth' => $userData]);
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

    /**
     * @param $emails
     * @return array
     */
    private function validateDisconnectData($emails) {
        $errors = [];
        /** @var ShopUser $user */
        $user = $this->getUser();

        $existUser = $this->userRepository->findOneBy(['username' => $emails['email']]);

        if ($emails['email'] != $emails['confirmEmail']) {
            $errors['email'] = 'app.ui.disconnect.facebook.error.not_same_email';
        }  else if ($existUser instanceof ShopUser) {
            if ($existUser->getId() != $user->getId()) {
                $errors['email'] = 'app.ui.change_email.error.email_is_used';
            }
        }

        return $errors;
    }

    /**
     * @param $passwords
     * @return array
     */
    private function validatePassword($passwords) {
        $errors = [];

        if ($passwords['password'] != $passwords['confirmPassword']) {
            $errors['password'] = 'app.ui.pre_change_email.error.not_same_password';
        }

        return $errors;
    }

    /**
     * Attach OAuth sign-in provider account to existing user.
     * @param UserInterface $user
     * @param array $response
     * @return RedirectResponse
     */
    private function updateUserByOAuthUserResponse(UserInterface $user, $response)
    {
        /** @var SyliusUserInterface $user */
        Assert::isInstanceOf($user, SyliusUserInterface::class);
        $em = $this->getDoctrine()->getManager();

        /** @var UserOAuthInterface $oauth */
        $oauth = $this->oauthFactory->createNew();
        $oauth->setIdentifier($response['identifier']);
        $oauth->setProvider($response['provider']);
        $oauth->setAccessToken($response['access_token']);
        $oauth->setRefreshToken($response['refresh_token']);

        $user->addOAuthAccount($oauth);

        $em->persist($user);
        $em->flush();

        $token = new UsernamePasswordToken($user, null, 'shop', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $this->container->get('session')->set('_security_shop', serialize($token));

        return $this->redirectToRoute('store_welcome');
    }
}
