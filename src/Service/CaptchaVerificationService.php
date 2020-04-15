<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CaptchaVerificationService service
 * @author Rormdar Zavala <rzavala@praga.ws>
 */

class CaptchaVerificationService
{
    /** @var ContainerInterface $container */
    private $container;

    /** @var $secret string */
    private $secret;

    /**
     * ToolsService constructor.
     * @param ContainerInterface $serviceContainer
     * @param $captchaSecret
     */
    public function __construct(ContainerInterface $serviceContainer, $captchaSecret)
    {
        $this->container = $serviceContainer;
        $this->secret = $captchaSecret;
    }

    public function verify($code): bool
    {
        $data = [
            'secret' => $this->secret,
            'response' => $code
        ];

        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);

        try {
            $response = json_decode(curl_exec($verify), true);

            return $response['success'] ?? false;
        } catch (\Exception $exception) {
            $this->container->get('logger')->error($exception->getMessage());

            return false;
        }
    }

}
