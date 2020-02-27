<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;

class MenuBuilder
{
    private $factory;

    /**
     * @param FactoryInterface $factory
     *
     * Add any other dependency you need
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createSidebarMenu()
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes'    => [
                'class'             => 'nav metismenu',
            ],
        ]);

        $menu->addChild('app.ui.dashboard', [
            'route' => 'dashboard_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-dashboard',
            'routes' => [
                'dashboard_index',
            ],
        ]);

        $menu->addChild('app.ui.order_history', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-shopping-cart',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.news_banner', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-newspaper-o',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.coupons', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-tags',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.send_push', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-bell',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.users', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-users',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.ratings', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-star',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.purchase_admin', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-cogs',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.locations', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-store',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.faqs', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-question-circle-o',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.about_procasa', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-th-large',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        $menu->addChild('app.ui.terms_and_conditions', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-file-alt',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        return $menu;
    }
}
