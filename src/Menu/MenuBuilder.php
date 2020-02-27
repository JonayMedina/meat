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
                'id' => 'side-menu'
            ],
        ]);

        /**
         * Dashboard
         */
        $menu->addChild('app.ui.dashboard', [
            'route' => 'dashboard_index',
        ])->setExtras([
            'icon' => 'dashboard',
            'routes' => [
                'dashboard_index',
            ],
        ]);

        /**
         * Order History
         */
        $menu->addChild('app.ui.order_history', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'orders',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        /**
         * Banners
         */
        $menu->addChild('app.ui.news_banner', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'slider',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        /**
         * Coupons
         */
        $menu->addChild('app.ui.coupons', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'coupons',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        /**
         * Send push
         */
        $menu
            ->addChild('app.ui.send_push', ['uri' => '#'])
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'push')
            ->setLinkAttributes([
                'aria-expanded' => 'false'
            ])
            ->setChildrenAttribute('class', 'nav nav-second-level collapse')
            ->setChildrenAttribute('aria-expanded', 'false')
            ->setChildrenAttribute('style', 'height: 0px;');

        $menu['app.ui.send_push']
            ->addChild('app.ui.send_push', ['route' => 'sylius_shop_homepage']);

        $menu['app.ui.send_push']
            ->addChild('app.ui.segments', ['route' => 'sylius_shop_homepage']);

        /**
         * Users
         */
        $menu->addChild('app.ui.users', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'users',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        /**
         * Ratings
         */
        $menu->addChild('app.ui.ratings', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'ratings',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        /**
         * Purchase admin
         */
        $menu
            ->addChild('app.ui.purchase_admin', ['uri' => '#'])
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'settings')
            ->setLinkAttributes([
                'aria-expanded' => 'false',
            ])
            ->setChildrenAttribute('class', 'nav nav-second-level collapse')
            ->setChildrenAttribute('aria-expanded', 'false')
            ->setChildrenAttribute('style', 'height: 0px;');

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.purchase_texts', ['route' => 'sylius_shop_homepage']);

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.purchase_settings', ['route' => 'sylius_shop_homepage']);

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.holidays', ['route' => 'sylius_shop_homepage']);

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.searcher', ['route' => 'sylius_shop_homepage']);

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.category_color', ['route' => 'sylius_shop_homepage']);

        /**
         * Locations
         */
        $menu->addChild('app.ui.locations', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'locations',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        /**
         * FAQs
         */
        $menu->addChild('app.ui.faqs', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'faqs',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        /**
         * About procasa
         */
        $menu->addChild('app.ui.about_procasa', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'about-us',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        /**
         * Terms and conditions
         */
        $menu->addChild('app.ui.terms_and_conditions', [
            'route' => 'sylius_shop_homepage',
        ])->setExtras([
            'icon' => 'terms',
            'routes' => [
                'sylius_shop_homepage',
            ],
        ]);

        return $menu;
    }
}
