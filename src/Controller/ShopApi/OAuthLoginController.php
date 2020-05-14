<?php

namespace App\Controller\ShopApi;

use Facebook\Facebook;
use App\Model\APIResponse;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use App\Entity\User\UserOAuth;
use App\Entity\Customer\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\Exceptions\FacebookSDKException;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var JWTTokenManagerInterface
     */
    private $JWTManager;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param JWTTokenManagerInterface $JWTManager
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, JWTTokenManagerInterface $JWTManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->JWTManager = $JWTManager;
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

        if (!$this->validateProvider($provider)) {
            $statusCode = Response::HTTP_BAD_REQUEST;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Bad provider: ' . $provider, []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $serverResponse = $this->validateAccessToken($provider, $identifier, $accessToken);
        $email = $serverResponse['email'];
        $firstName = $serverResponse['first_name'];
        $lastName = $serverResponse['last_name'];

        if (null === $serverResponse) {
            $statusCode = Response::HTTP_UNAUTHORIZED;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Unauthorized', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $oauthUser = $this->getOAuthUser($provider, $identifier);

        if (!$oauthUser instanceof UserInterface) {
            $oauthUser = $this->createUser($provider, $identifier, $accessToken, $firstName, $lastName, $email);
        }

        $jwt = $this->JWTManager->create($oauthUser);

        $statusCode = Response::HTTP_OK;
        $response = [ 'token' => $jwt ];

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
     * @return array|null
     */
    private function validateAccessToken($provider, $identifier, $accessToken): ?array
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
            // TODO: implement Apple ID login.
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

        $this->entityManager->persist($shopUser);
        $this->entityManager->persist($userOAuth);

        $this->entityManager->flush();

        return $shopUser;
    }
}
