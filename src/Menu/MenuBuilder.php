<?php

namespace App\Menu;

use App\Entity\User\AdminUser;
use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class MenuBuilder
 * @package App\Menu
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class MenuBuilder
{

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param FactoryInterface $factory
     *
     * Add any other dependency you need
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->factory = $factory;
        $this->authorizationChecker = $authorizationChecker;
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
            'route' => 'orders_index',
        ])->setExtras([
            'icon' => 'orders',
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
            'icon' => 'slider',
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
            'icon' => 'coupons',
            'routes' => [
                'coupons_index',
                'coupons_show',
                'coupons_edit',
                'coupons_new',
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
                    'segments_new',
                    'segments_edit',
                ],
            ])
            ->setAttribute('second-level', 'true');

        /**
         * Users: Only if has role admin
         */
        if ($this->authorizationChecker->isGranted(AdminUser::ROLE_ADMIN)) {
            $menu->addChild('app.ui.users', [
                'route' => 'users_index',
            ])->setExtras([
                'icon' => 'users',
                'routes' => [
                    'users_index',
                    'users_edit',
                    'users_new',
                    'users_show',
                ],
            ]);
        }

        /**
         * Ratings
         */
        $menu->addChild('app.ui.ratings', [
            'route' => 'ratings_index',
        ])->setExtras([
            'icon' => 'ratings',
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
            ->setAttribute('icon', 'settings')
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
                    'purchase_settings_edit',
                ],
            ])
            ->setAttribute('second-level', 'true');

        $menu['app.ui.purchase_admin']
            ->addChild('app.ui.holidays', ['route' => 'holidays'])
            ->setExtras([
                'routes' => [
                    'holidays',
                    'holidays_calendar',
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
            'icon' => 'locations',
            'routes' => [
                'locations_index',
                'locations_edit',
                'locations_new',
            ],
        ]);

        /**
         * FAQs
         */
        $menu->addChild('app.ui.faqs', [
            'route' => 'faqs_index',
        ])->setExtras([
            'icon' => 'faqs',
            'routes' => [
                'faqs_index',
                'faqs_new',
                'faqs_edit',
                'faqs_show',
            ],
        ]);

        /**
         * About procasa
         */
        $menu->addChild('app.ui.about_procasa', [
            'route' => 'about_index',
        ])->setExtras([
            'icon' => 'about-us',
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
            'icon' => 'terms',
            'routes' => [
                'terms_index',
            ],
        ]);

        return $menu;
    }
}
