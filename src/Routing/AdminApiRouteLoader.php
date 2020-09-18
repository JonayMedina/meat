<?php

namespace App\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class AdminApiRouteLoader extends Loader
{
    private $isLoaded = false;

    public function load($resource, $type = null)
    {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();

        foreach ($this->getRoutes() as $item) {

            $defaults = [
                '_controller' => 'App\Controller\ExtraController::extra',
            ];

            $requirements = [];
            $route = new Route($item['path'], $defaults, $requirements);
            $routes->add($item['name'], $route);

        }

        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }

    private function getRoutes()
    {
        return [
            [
                'path' => '/v1/orders/{id}',
                'name' => 'admin_api_orders_show',
            ],
            [
                'path' => '/v1/addresses/{id}',
                'name' => 'admin_api_address_show',
            ],
            [
                'path' => '/v1/customers/{id}',
                'name' => 'admin_api_customers_show',
            ],
        ];
    }
}
