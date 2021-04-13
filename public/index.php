<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

$request = Request::createFromGlobals();

//if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
//    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
//}

Request::setTrustedProxies(
// trust *all* requests
    ['127.0.0.1', $request->server->get('REMOTE_ADDR')],
    // if you're using ELB, otherwise see https://symfony.com/doc/current/request/load_balancer_reverse_proxy.html
    Request::HEADER_X_FORWARDED_AWS_ELB
);

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
