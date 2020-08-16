<?php

namespace App\Controller\ShopApi;

use Facebook\Facebook;
use GuzzleHttp\Client;
use App\Model\APIResponse;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use App\Entity\User\UserOAuth;
use App\Entity\Customer\Customer;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Facebook\Exceptions\FacebookSDKException;
use Symfony\Component\HttpFoundation\Request;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Facebook\Exceptions\FacebookResponseException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

/**
 * OAuthLoginController
 * @Route("/oauth-login")
 */
class OAuthLoginController extends AbstractFOSRestController
{
    const PROVIDER_FACEBOOK = 'facebook';
    const PROVIDER_APPLE = 'apple';

    /** @var LoggerInterface $logger */
    private $logger;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var JWTTokenManagerInterface */
    private $JWTManager;

    /** @var string */
    private $appleClientId;

    /** @var string */
    private $appleClientSecret;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param JWTTokenManagerInterface $JWTManager
     * @param string $appleClientId
     * @param string $appleClientSecret
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, JWTTokenManagerInterface $JWTManager, string $appleClientId, string $appleClientSecret)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->JWTManager = $JWTManager;
        $this->appleClientId = $appleClientId;
        $this->appleClientSecret = $appleClientSecret;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_oauth_login",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $identifier = $request->get('identifier');
        $accessToken = $request->get('access_token');
        $provider = $request->get('provider');
        $method = 'login';
        $email = $request->get('email');
        $firstName = $request->get('first_name');
        $lastName = $request->get('last_name');

        if (!$this->validateProvider($provider)) {
            $statusCode = Response::HTTP_BAD_REQUEST;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Bad provider: ' . $provider, []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $serverResponse = $this->validateAccessToken($provider, $identifier, $accessToken);

        if (null === $serverResponse) {
            $statusCode = Response::HTTP_UNAUTHORIZED;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Unauthorized', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        if ($provider == self::PROVIDER_FACEBOOK) {
            $email = $serverResponse['email'];
            $firstName = $serverResponse['first_name'];
            $lastName = $serverResponse['last_name'];
        }

        $oauthUser = $this->getOAuthUser($provider, $identifier);

        if (!$oauthUser instanceof UserInterface) {
            if ($email == null || $firstName == null || $lastName == null) {
                $statusCode = Response::HTTP_BAD_REQUEST;

                $message = 'Missing information: ';

                if ($email == null) {
                    $message .= ' Email, ';
                }

                if ($firstName == null) {
                    $message .= ' First name, ';
                }

                if ($lastName == null) {
                    $message .= ' Last name';
                }

                $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $message, []);
                $view = $this->view($response, $statusCode);

                return $this->handleView($view);
            }

            $oauthUser = $this->createUser($provider, $identifier, $accessToken, $firstName, $lastName, $email);
        }

        $jwt = $this->JWTManager->create($oauthUser);

        $statusCode = Response::HTTP_OK;
        $response = [ 'token' => $jwt ];

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * Merge accounts
     * @Route(
     *     "/merge.{_format}",
     *     name="shop_api_oauth_merge",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function mergeAction(Request $request)
    {
        $customer_id = $request->get('customer_id');
        $identifier = $request->get('identifier');
        $accessToken = $request->get('access_token');
        $provider = $request->get('provider');

        if (!$this->validateProvider($provider)) {
            $statusCode = Response::HTTP_BAD_REQUEST;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Bad provider: ' . $provider, []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $serverResponse = $this->validateAccessToken($provider, $identifier, $accessToken);

        if (null === $serverResponse) {
            $statusCode = Response::HTTP_UNAUTHORIZED;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Unauthorized', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        /**
         * Everything is valid...
         * @var Customer $customer
         */
        $customer = $this->entityManager->getRepository('App:Customer\Customer')
            ->find($customer_id);

        $shopUser = $customer->getUser();

        if (null === $this->getOAuthUser($provider, $identifier)) {
            /** Create User OAuth */
            $userOAuth = new UserOAuth();
            $userOAuth->setProvider($provider);
            $userOAuth->setUser($shopUser);
            $userOAuth->setIdentifier($identifier);
            $userOAuth->setAccessToken($accessToken);

            $this->entityManager->persist($userOAuth);
            $this->entityManager->flush();
        }

        $statusCode = Response::HTTP_CREATED;
        $response = [];

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * Check if given provider is valid.
     * @param $provider
     * @return bool
     */
    private function validateProvider($provider): bool
    {
        if (in_array($provider, [self::PROVIDER_APPLE, self::PROVIDER_FACEBOOK])) {
            return true;
        }

        return false;
    }

    /**
     * Validate if access token is correct, if correct, return email.
     * @param $provider
     * @param $identifier
     * @param $accessToken
     * @param bool $isRegister
     * @return array|bool|null
     * @throws GuzzleException
     */
    private function validateAccessToken($provider, $identifier, $accessToken, $isRegister = false)
    {
        if (self::PROVIDER_FACEBOOK === $provider) {
            try {
                $fb = new Facebook([
                    'app_id' => $this->getParameter('fb_client_id'),
                    'app_secret' => $this->getParameter('fb_client_secret'),
                    'default_access_token' => $accessToken,
                ]);

                $response = $fb->get('/me?fields=email,first_name,last_name');
                $decodedBody = $response->getDecodedBody();

                if ($decodedBody['id'] != $identifier) {
                    return null;
                }

                return $decodedBody;
            } catch(FacebookResponseException $exception) {
                $this->logger->error($exception->getMessage());

                return null;
            } catch(FacebookSDKException $exception) {
                $this->logger->error($exception->getMessage());

                return null;
            }
        }

        if (self::PROVIDER_APPLE === $provider) {
            if ($isRegister) {
                $client = new Client();

                try {
                    $response = $client->request(
                        'POST',
                        'https://appleid.apple.com/auth/token',
                        [
                            'form_params' => [
                                'client_id' => $this->appleClientId,
                                'client_secret' => $this->appleClientSecret,
                                'code' => $accessToken,
                                'grant_type' => 'authorization_code'
                            ]
                        ]
                    );

                    $headers = $response->getHeaders();
                    $body = json_decode($response->getBody(), true);

                    if ($body['access_token'] == $accessToken) {
                        return true;
                    } else {
                        return null;
                    }
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getMessage());

                    return null;
                }
            } else {
                $oauthUser = $this->entityManager->getRepository('App:User\UserOAuth')
                    ->findOneBy(['provider' => $provider, 'identifier' => $identifier]);

                if ($oauthUser instanceof UserOAuth) {
                    if ($oauthUser->isVerified()) {
                        return true;
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Find OAuth User.
     * @param $provider
     * @param $identifier
     * @return UserInterface|null
     */
    private function getOAuthUser($provider, $identifier): ?UserInterface
    {
        $oauthUser = $this->entityManager->getRepository('App:User\UserOAuth')
            ->findOneBy(['provider' => $provider, 'identifier' => $identifier]);

        if ($oauthUser instanceof UserOAuth) {
            return $oauthUser->getUser();
        }

        return null;
    }

    /**
     * Create User
     * @param $provider
     * @param $identifier
     * @param $accessToken
     * @param $firstName
     * @param $lastName
     * @param $email
     * @return UserInterface|null
     */
    private function createUser($provider, $identifier, $accessToken, $firstName, $lastName, $email): ?UserInterface
    {
        $shopUser = $this->entityManager->getRepository('App:User\ShopUser')
            ->findOneBy(['username' => $email]);

        if (!$shopUser instanceof ShopUser) {
            /** Create Customer */
            $customer = new Customer();
            $customer->setFirstName($firstName);
            $customer->setLastName($lastName);
            $customer->setEmail($email);

            $this->entityManager->persist($customer);

            /** Create Shop User */
            $shopUser = new ShopUser();
            $shopUser->setCustomer($customer);
            $shopUser->setPlainPassword(md5(time()));
            $shopUser->setUsername($email);
            $shopUser->setEmail($email);
            $shopUser->setEnabled(true);
            $shopUser->setVerifiedAt(new \DateTime());
            $shopUser->addRole(ShopUser::DEFAULT_ROLE);
            $shopUser->setTermsAndConditionsAcceptedAt(new \DateTime());
        }

        /** Create User OAuth */
        $userOAuth = new UserOAuth();
        $userOAuth->setProvider($provider);
        $userOAuth->setUser($shopUser);
        $userOAuth->setIdentifier($identifier);
        $userOAuth->setAccessToken($accessToken);

        if ($provider == self::PROVIDER_APPLE) {
            $userOAuth->setIsVerified(true);
        }

        $this->entityManager->persist($shopUser);
        $this->entityManager->persist($userOAuth);

        $this->entityManager->flush();

        return $shopUser;
    }
}
