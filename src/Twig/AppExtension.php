<?php

namespace App\Twig;

use Exception;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Holiday;
use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use App\Entity\User\UserOAuth;
use App\Entity\Taxonomy\Taxon;
use App\Service\HistoryService;
use App\Entity\Product\Product;
use App\Service\UploaderHelper;
use App\Service\FavoriteService;
use App\Service\SettingsService;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use App\Entity\Promotion\Promotion;
use App\Repository\HolidayRepository;
use Twig\Extension\AbstractExtension;
use App\Repository\FavoriteRepository;
use App\Entity\Channel\ChannelPricing;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Promotion\Model\PromotionActionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AppExtension
 * @package App\Twig
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class AppExtension extends AbstractExtension
{
    /** @var ContainerInterface $container */
    private $container;

    /** @var UploaderHelper $uploaderHelper */
    private $uploaderHelper;

    /** @var SettingsService $settingsService */
    private $settingsService;

    /** @var TranslatorInterface */
    private $translator;

    /** @var FavoriteService */
    private $favoriteService;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ChannelContextInterface */
    private $channelContext;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var FavoriteRepository */
    private $favoriteRepository;

    /** @var HistoryService */
    private $historyService;

    /** @var HolidayRepository */
    private $holidayRepository;

    /**
     * AppExtension constructor.
     * @param ContainerInterface $container
     * @param UploaderHelper $uploaderHelper
     * @param SettingsService $settingsService
     * @param TranslatorInterface $translator
     * @param FavoriteService $favoriteService
     * @param TokenStorageInterface $tokenStorage
     * @param ChannelContextInterface $channelContext
     * @param OrderRepositoryInterface $orderRepository
     * @param FavoriteRepository $favoriteRepository
     * @param HistoryService $historyService
     * @param HolidayRepository $holidayRepository
     */
    public function __construct(ContainerInterface $container, UploaderHelper $uploaderHelper, SettingsService $settingsService, TranslatorInterface $translator, FavoriteService $favoriteService, TokenStorageInterface $tokenStorage, ChannelContextInterface $channelContext, OrderRepositoryInterface $orderRepository, FavoriteRepository $favoriteRepository, HistoryService $historyService, HolidayRepository $holidayRepository)
    {
        $this->container = $container;
        $this->uploaderHelper = $uploaderHelper;
        $this->settingsService = $settingsService;
        $this->translator = $translator;
        $this->favoriteService = $favoriteService;
        $this->tokenStorage = $tokenStorage;
        $this->channelContext = $channelContext;
        $this->orderRepository = $orderRepository;
        $this->favoriteRepository = $favoriteRepository;
        $this->historyService = $historyService;
        $this->holidayRepository = $holidayRepository;
    }

    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
            new TwigFilter('base64', [$this, 'imageToBase64']),
            new TwigFilter('translated_roles', [$this, 'translatedRoles']),
            new TwigFilter('is_favorite', [$this, 'isFavorite']),
            new TwigFilter('shipping', [$this, 'getShippingAddresses']),
            new TwigFilter('card_mask', [$this, 'creditMask']),
            new TwigFilter('mask', [$this, 'mask']),
            new TwigFilter('phone_format', [$this, 'phoneFormat']),
            new TwigFilter('format_schedule', [$this, 'formatSchedules']),
        ];
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('get_url', [$this, 'getUrl']),
            new TwigFunction('uploaded_location_asset', [$this, 'getUploadedLocationAssetPath']),
            new TwigFunction('uploaded_banner_asset', [$this, 'getUploadedBannerAssetPath']),
            new TwigFunction('about_store', [$this, 'aboutStore']),
            new TwigFunction('get_price', [$this, 'getPrice']),
            new TwigFunction('get_principal_taxon', [$this, 'getPrincipalTaxon']),
            new TwigFunction('get_coupon_action', [$this, 'getCouponAction']),
            new TwigFunction('has_orders', [$this, 'userHasOrders']),
            new TwigFunction('get_n_favorites', [$this, 'getNFavorites']),
            new TwigFunction('last_order', [$this, 'getLastOrder']),
            new TwigFunction('connected_to_provider', [$this, 'isConnectedToProvider']),
            new TwigFunction('get_history', [$this, 'getOrders']),
            new TwigFunction('get_holidays', [$this, 'getHolidays']),
        ];
    }

    /**
     * @param Product $product
     * @param ShopUser|null $user
     * @return bool
     */
    public function isFavorite(Product $product, ShopUser $user = null)
    {
        $user = ($user instanceof ShopUser) ? $user : $this->getUser();

        return $this->favoriteService->isFavorite($product, $user);
    }

    /**
     * @param $roles
     * @return string|string[]
     */
    public function translatedRoles($roles)
    {
        $string = '';

        foreach ($roles as $role) {
            $string .= $this->translator->trans('app.ui.roles.' . strtolower($role)) . ', ';
        }

        return substr_replace($string ,"", -2);
    }

    public function getUploadedBannerAssetPath($path)
    {
        return $this->getUploadedAssetPath( UploaderHelper::BANNER_PHOTO_IMAGE . '/' . $path);
    }

    /**
     * @param $path
     * @return string
     */
    public function getUploadedLocationAssetPath($path)
    {
        return $this->getUploadedAssetPath( UploaderHelper::LOCATION_IMAGE . '/' . $path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getUploadedAssetPath(string $path): string
    {
        return $this->uploaderHelper
            ->getPublicPath($path);
    }

    /**
     * @param $number
     * @return float|int|string
     */
    public function formatPrice($number)
    {
        return $number / 100;
    }

    /**
     * @param $url
     * @return string
     */
    public function imageToBase64($url) {
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );

        $type = pathinfo($url, PATHINFO_EXTENSION);
        $data = file_get_contents($url, false, stream_context_create($arrContextOptions));

        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    /**
     * Return Main URL.
     * @param string|null $url
     * @return string
     */
    public function getUrl(?string $url)
    {
        return getenv('APP_URL') . $url;
    }

    /**
     * @param string $option
     * @return string|null
     */
    public function aboutStore($option = 'about-us') {
        switch ($option) {
            case 'facebook': return $this->settingsService->getFacebookUrl();
            case 'instagram': return $this->settingsService->getInstagramUrl();
            case 'twitter': return $this->settingsService->getTwitterUrl();
            case 'pinterest': return $this->settingsService->getPinterestUrl();
            case 'app-store': return $this->settingsService->getAppStoreUrl();
            case 'play-store': return $this->settingsService->getPlayStoreUrl();
            case 'phrase': return $this->settingsService->getPhrase();
            case 'author': return $this->settingsService->getAuthor();
            case 'complaints-email': return $this->settingsService->getComplaintsEmail();
            case 'contact-email': return $this->settingsService->getContactEmail();
            case 'theme': return $this->settingsService->getTheme();
            case 'delivery-hours': return $this->settingsService->getDeliveryHours();
            case 'show-search': return $this->settingsService->isShowProductSearchBox();
            case 'days-to-choose': return $this->settingsService->getDaysToChooseInAdvanceToPurchase();
            case 'first-purchase-ms': return $this->settingsService->getFirstPurchaseMessage();
            case 'new-address-ms': return $this->settingsService->getNewAddressMessage();
            case 'max-purchase': return $this->settingsService->getMaximumPurchaseValue();
            case 'min-purchase': return $this->settingsService->getMinimumPurchaseValue();
            case 'phone': return $this->settingsService->getPhoneNumber();
            default:
                return $this->settingsService->getAboutUs();
        }
    }

    /**
     * Return current user.
     * @return ShopUser|null
     */
    private function getUser(): ?ShopUser
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return ($user instanceof ShopUser) ? $user : null;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getPrice(Product $product) {
        /**
         * @var ChannelPricing $channelPricing
         */
        $channelPricing = $product->getVariants()[0]->getChannelPricings()[$this->channelContext->getChannel()->getCode()];

        if ($channelPricing->getOriginalPrice() > $channelPricing->getPrice()) {
            return ['isOffer' => true, 'price' => $channelPricing->getPrice(), 'originalPrice' => $channelPricing->getOriginalPrice()];
        } else {
            return ['isOffer' => false, 'price' => $channelPricing->getPrice()];
        }
    }

    /**
     * @param Taxon $taxon
     * @return Taxon|null
     */
    public function getPrincipalTaxon(Taxon $taxon) {
        while ($taxon->getParent() != null) {
            $taxon = $taxon->getParent();
        }

        return $taxon;
    }

    /**
     * @param Promotion $promotion
     * @return array
     */
    public function getCouponAction(Promotion $promotion) {
        /**
         * @var PromotionActionInterface[]
         */
        $actions = $promotion->getActions();

        return $actions[0];
    }

    public function userHasOrders(ShopUser $user) {
        $orders = $this->orderRepository->findBy(['customer' => $user->getCustomer(), 'state' => Order::STATE_NEW]);

        return count($orders) > 0;
    }

    /**
     * @param ShopUser $user
     * @param $limit
     * @return mixed
     */
    public function getNFavorites(ShopUser $user, $limit) {
        return $this->favoriteRepository->findBy(['shopUser' => $user], null, $limit);
    }

    /**
     * @param $addresses
     * @return array
     */
    public function getShippingAddresses($addresses) {
        /** @var Address $address1 */
        $address1 = $addresses[0];
        /** @var Customer $customer */
        $customer = $this->getUser()->getCustomer();
        $default = $customer->getDefaultAddress();
        $new = [];

        if ($default) {
            $new[] = $default;
        }

        foreach ($addresses as $address ) {
            if ($default) {
                if ($default->getId() != $address->getId()) {
                    if ($address->getType() == Address::TYPE_SHIPPING) {
                        $new[] = $address;
                    }
                }
            } else {
                if ($address->getType() == Address::TYPE_SHIPPING) {
                    $new[] = $address;
                }
            }
        }

        return $new;
    }

    /**
     * @param $text
     * @return string
     */
    public function creditMask($text) {
        $text = str_replace(" ", "", $text);
        $mask = str_repeat("*", strlen($text)-4) . substr($text, -4);
        $array = str_split($mask, 4);

        return implode(" ", $array);
    }

    /**
     * @param $str
     * @return string|string[]
     */
    function mask($str) {
        $start = 0;
        $length = null;
        $mask = preg_replace("/\S/", "*", $str);

        if (is_null($length)) {
            $mask = substr($mask, $start);
            $str = substr_replace($str, $mask, $start);
        } else {
            $mask = substr($mask, $start, $length);
            $str = substr_replace($str, $mask, $start, $length);
        }

        return $str;
    }

    /**
     * @param $str
     * @return string|string[]|null
     */
    function phoneFormat($str) {
        $str = str_replace(' ', '-', $str);

        if (strpos($str, '-'))  {
            return $str;
        } else {
            return preg_replace('/\d{4}/', '$0-', str_replace('.', null, trim($str)), 1);
        }
    }

    /**
     * @param ShopUser $user
     * @return object|null
     * @throws NonUniqueResultException
     */
    public function getLastOrder(ShopUser $user) {
        return $this->historyService->getLastOrder($user);
    }

    /**
     * @param ShopUser $user
     * @param $provider
     * @return bool
     */
    public function isConnectedToProvider(ShopUser $user, $provider) {
        $oauthUser = $this->container->get('doctrine')->getManager()->getRepository('App:User\UserOAuth')
            ->findOneBy(['provider' => $provider, 'user' => $user]);

        if ($oauthUser instanceof UserOAuth) {
            return true;
        }

        return false;
    }

    /**
     * @param ShopUser $user
     * @return Order[]
     */
    public function getOrders(ShopUser $user) {
        return $this->historyService->getOrderHistory($user);
    }

    /**
     * @return Holiday[]
     */
    public function getHolidays() {
        return $this->holidayRepository->findAll();
    }

    /**
     * @param $schedules
     * @return array
     */
    public function formatSchedules($schedules) {
        $formatted = [];

        foreach ($schedules as $schedule) {
            if ($schedule['enabled'] == '1') {
                $formatted[] = $this->translator->trans('app.ui.schedule.%schedule%_%start%_%end%', ['%schedule%' => $schedule['name'], '%start%' => $schedule['start'], '%end%' => $schedule['end']]);
            }
        }

        return $formatted;
    }
}
