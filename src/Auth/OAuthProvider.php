<?php

namespace App\Auth;

use Webmozart\Assert\Assert;
use App\Entity\User\ShopUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\User\Model\UserOAuthInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sylius\Component\User\Canonicalizer\CanonicalizerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use Sylius\Component\Core\Repository\CustomerRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Sylius\Component\Core\Model\ShopUserInterface as SyliusUserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class OAuthProvider
 * @package App\Auth
 */
class OAuthProvider extends OAuthUserProvider
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var RepositoryInterface */
    private $oauthRepository;

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

    /** @var SenderInterface */
    private $sender;

    /** @var SessionInterface */
    private $session;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var RouterInterface */
    private $router;

    /**
     * OAuthProvider constructor.
     * @param EntityManagerInterface $em
     * @param RepositoryInterface $oauthRepository
     * @param UserRepositoryInterface $userRepository
     * @param FactoryInterface $customerFactory
     * @param FactoryInterface $userFactory
     * @param CanonicalizerInterface $canonicalizer
     * @param CustomerRepositoryInterface $customerRepository
     * @param FactoryInterface $oauthFactory
     * @param SenderInterface $sender
     * @param SessionInterface $session
     * @param RouterInterface $router
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManagerInterface $em, RepositoryInterface $oauthRepository, UserRepositoryInterface $userRepository, FactoryInterface $customerFactory, FactoryInterface $userFactory, CanonicalizerInterface $canonicalizer, CustomerRepositoryInterface $customerRepository, FactoryInterface $oauthFactory, SenderInterface $sender, SessionInterface $session, RouterInterface $router, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->oauthRepository = $oauthRepository;
        $this->userRepository = $userRepository;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        $this->canonicalizer = $canonicalizer;
        $this->customerRepository = $customerRepository;
        $this->oauthFactory = $oauthFactory;
        $this->sender = $sender;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }

    /**
     * @param UserResponseInterface $response
     * @return UserInterface
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $oauth = $this->oauthRepository->findOneBy([
            'provider' => $response->getResourceOwner()->getName(),
            'identifier' => $response->getUsername(),
        ]);

        if ($oauth instanceof UserOAuthInterface) {
            return $oauth->getUser();
        }

        if (null !== $response->getEmail()) {
            $user = $this->userRepository->findOneByEmail($response->getEmail());

            if ($user instanceof ShopUser) {
                return $this->updateUserByOAuthUserResponse($user, $response, 'sylius_shop_account_dashboard');
            }

            return $this->createUserByOAuthUserResponse($response);
        }

        throw new UsernameNotFoundException('Email is null or not provided');
    }

    /**
     * @param UserInterface $user
     * @param UserResponseInterface $response
     */
    public function connect(UserInterface $user, UserResponseInterface $response): void
    {
        $this->updateUserByOAuthUserResponse($user, $response);
    }

    /**
     * @param UserResponseInterface $response
     * @return SyliusUserInterface
     */
    private function createUserByOAuthUserResponse(UserResponseInterface $response)
    {
        /** @var SyliusUserInterface $user */
        $user = $this->userFactory->createNew();

        $canonicalEmail = $this->canonicalizer->canonicalize($response->getEmail());

        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->findOneBy(['emailCanonical' => $canonicalEmail]);

        if (null === $customer) {
            /** @var CustomerInterface $customer */
            $customer = $this->customerFactory->createNew();
        }

        $user->setCustomer($customer);

        // set default values taken from OAuth sign-in provider account
        if (null !== $email = $response->getEmail()) {
            $customer->setEmail($email);
        }

        if (null !== $name = $response->getFirstName()) {
            $customer->setFirstName($name);
        } elseif (null !== $realName = $response->getRealName()) {
            $customer->setFirstName($realName);
        }

        if (null !== $lastName = $response->getLastName()) {
            $customer->setLastName($lastName);
        }

        if (!$user->getUsername()) {
            $user->setUsername($response->getEmail() ?: $response->getNickname());
        }

        // set random password to prevent issue with not nullable field & potential security hole
        $user->setPlainPassword(substr(sha1($response->getAccessToken()), 0, 10));

        $user->setEnabled(true);

        $this->sender->send('user_registration', [$customer->getEmail()], ['user' => $user]);

        return $this->updateUserByOAuthUserResponse($user, $response, 'store_welcome');
    }

    /**
     * Attach OAuth sign-in provider account to existing user.
     * @param UserInterface $user
     * @param UserResponseInterface $response
     * @param string $redirect
     * @return SyliusUserInterface
     */
    private function updateUserByOAuthUserResponse(UserInterface $user, UserResponseInterface $response, string $redirect = '')
    {
        /** @var SyliusUserInterface $user */
        Assert::isInstanceOf($user, SyliusUserInterface::class);

        /** @var UserOAuthInterface $oauth */
        $oauth = $this->oauthFactory->createNew();
        $oauth->setIdentifier($response->getUsername());
        $oauth->setProvider($response->getResourceOwner()->getName());
        $oauth->setAccessToken($response->getAccessToken());
        $oauth->setRefreshToken($response->getRefreshToken());

        $user->addOAuthAccount($oauth);

        $this->em->persist($user);
        $this->em->flush();

        if ($redirect != '') {
            $this->session->set('redirect_to_route', $redirect);
        }

        return $user;
    }
}
