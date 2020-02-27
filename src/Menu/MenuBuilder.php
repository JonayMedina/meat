<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;

/**
 * Class MenuBuilder
 * @package App\Menu
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
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
            'icon' => 'fa fa-lg fa-fw fa-chart-pie',
            'routes' => [
                'dashboard_index',
            ],
        ]);

        /**
         * Order History
         */
        $menu->addChild('app.ui.order_history', [
            'route' => 'orders_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-shopping-cart',
            'routes' => [
                'orders_index',
            ],
        ]);

        /**
         * Banners
         */
        $menu->addChild('app.ui.news_banner', [
            'route' => 'banners_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-newspaper-o',
            'routes' => [
                'banners_index',
            ],
        ]);

        /**
         * Coupons
         */
        $menu->addChild('app.ui.coupons', [
            'route' => 'coupons_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-tags',
            'routes' => [
                'coupons_index',
            ],
        ]);

        /**
         * Send push
         */
        $menu
            ->addChild('app.ui.send_push', ['uri' => '#'])
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'fa fa-lg fa-fw fa-bell')
            ->setLinkAttributes([
                'aria-expanded' => 'false'
            ])
            ->setChildrenAttribute('class', 'nav nav-second-level collapse')
            ->setChildrenAttribute('aria-expanded', 'false')
            ->setChildrenAttribute('style', 'height: 0px;');

        $menu['app.ui.send_push']
            ->addChild('app.ui.send_push', ['route' => 'push_index'])
            ->setExtras([
                'routes' => [
                    'push_index',
                ],
            ])
            ->setAttribute('second-level', 'true');

        $menu['app.ui.send_push']
            ->addChild('app.ui.segments', ['route' => 'segments_index'])
            ->setExtras([
                'routes' => [
                    'segments_index',
                ],
            ])
            ->setAttribute('second-level', 'true');

        /**
         * Users
         */
        $menu->addChild('app.ui.users', [
            'route' => 'users_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-users',
            'routes' => [
                'users_index',
            ],
        ]);

        /**
         * Ratings
         */
        $menu->addChild('app.ui.ratings', [
            'route' => 'ratings_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-star',
            'routes' => [
                'ratings_index',
            ],
        ]);

        /**
         * Purchase admin
         */
        $menu
            ->addChild('app.ui.purchase_admin', ['uri' => '#'])
            ->setAttribute('dropdown', true)
            ->setAttribute('icon', 'fa fa-lg fa-fw fa-cogs')
            ->setLinkAttributes([
                'aria-expanded' => 'false',
            ])
            ->setChildrenAttribute('class', 'nav nav-second-level collapse')
            ->setChildrenAttribute('aria-expanded', 'false')
            ->setChildrenAttribute('style', 'height: 0px;');

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.purchase_texts', ['route' => 'purchase_texts'])
            ->setExtras([
                'routes' => [
                    'purchase_texts',
                ],
            ])
            ->setAttribute('second-level', 'true');

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.purchase_settings', ['route' => 'purchase_settings'])
            ->setExtras([
                'routes' => [
                    'purchase_settings',
                ],
            ])
            ->setAttribute('second-level', 'true');

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.holidays', ['route' => 'holidays'])
            ->setExtras([
                'routes' => [
                    'holidays',
                ],
            ])
            ->setAttribute('second-level', 'true');

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.searcher', ['route' => 'searcher'])
            ->setExtras([
                'routes' => [
                    'searcher',
                ],
            ])
            ->setAttribute('second-level', 'true');

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.category_color', ['route' => 'category_color'])
            ->setExtras([
                'routes' => [
                    'category_color'
                ],
            ])
            ->setAttribute('second-level', 'true');

        /**
         * Locations
         */
        $menu->addChild('app.ui.locations', [
            'route' => 'locations_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-store',
            'routes' => [
                'locations_index',
            ],
        ]);

        /**
         * FAQs
         */
        $menu->addChild('app.ui.faqs', [
            'route' => 'faqs_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-question-circle-o',
            'routes' => [
                'faqs_index',
            ],
        ]);

        /**
         * About procasa
         */
        $menu->addChild('app.ui.about_procasa', [
            'route' => 'about_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-th-large',
            'routes' => [
                'about_index',
            ],
        ]);

        /**
         * Terms and conditions
         */
        $menu->addChild('app.ui.terms_and_conditions', [
            'route' => 'terms_index',
        ])->setExtras([
            'icon' => 'fa fa-lg fa-fw fa-file-alt',
            'routes' => [
                'terms_index',
            ],
        ]);

        return $menu;
    }
}
