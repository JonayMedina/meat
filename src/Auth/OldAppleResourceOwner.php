<?php

namespace App\Auth;

use Symfony\Component\HttpFoundation\Request;
use HWI\Bundle\OAuthBundle\Security\OAuthErrorHandler;
use Symfony\Component\OptionsResolver\OptionsResolver;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;

/**
 * Class AppleResourceOwner
 * @package App\Auth
 */
class OldAppleResourceOwner extends GenericOAuth2ResourceOwner
{
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'name',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'realname' => 'realname',
        'email' => 'email',
    );

    /**
     * @param array $accessToken
     * @param array $extraParameters
     * @return UserResponseInterface|PathUserResponse
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $response = $this->getUserResponse();
        $response->setData($accessToken);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * @param Request $request
     * @param mixed $redirectUri
     * @param array $extraParameters
     * @return array
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        OAuthErrorHandler::handleOAuthError($request);

        $parameters = array_merge(array(
            'code' => $request->request->get('code'),
            'grant_type' => 'authorization_code',
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
            'redirect_uri' => $redirectUri,
        ), $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        $user = $request->request->get('user', []);
        if(!is_object($user))
            $user = json_decode($user, true);
        $data = self::jwt_decode($response['id_token']);
        $response['id'] = $data['sub'];
        $response['firstname'] = $user['name']['firstName'] ?? null;
        $response['lastname'] = $user['name']['lastName'] ?? null;
        $response['realname'] = ($user['name']['firstName'] ?? null).' '.($user['name']['lastName'] ?? null);
        $response['nickname'] = str_replace(' ', '.', ($user['name']['firstName'] ?? null).'.'.($user['name']['lastName'] ?? null));
        $response['name'] = str_replace(' ', '.', ($user['name']['firstName'] ?? null).'.'.($user['name']['lastName'] ?? null));
        $response['email'] = $user['email'] ?? null;

        return $response;
    }

    /**
     * @param $jwt
     * @return mixed
     */
    private static function jwt_decode($jwt)
    {
        $tks = explode('.', $jwt);
        list($headb64, $bodyb64, $cryptob64) = $tks;
        return json_decode(self::urlsafeB64Decode($bodyb64), true);
    }

    /**
     * @param $input
     * @return false|string
     */
    private static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function handles(Request $request)
    {
        return $request->request->has('code');
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://appleid.apple.com/auth/authorize?response_mode=form_post',
            'access_token_url' => 'https://appleid.apple.com/auth/token',
            'revoke_token_url' => '',
            'infos_url' => '',
            'use_commas_in_scope' => false,
            'display' => null,
            'type' => 'oauth2',
            'user_response_class' => 'HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
            'scope' => 'name email',
            'appsecret_proof' => false,
        ]);
    }
}
