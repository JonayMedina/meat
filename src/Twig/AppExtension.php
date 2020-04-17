<?php

namespace App\Twig;

use Exception;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\User\ShopUser;
use App\Service\UploaderHelper;
use App\Entity\Product\Product;
use App\Service\FavoriteService;
use App\Service\SettingsService;
use Twig\Extension\AbstractExtension;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    /**
     * @var UploaderHelper $uploaderHelper
     */
    private $uploaderHelper;

    /** @var SettingsService $settingsService */
    private $settingsService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FavoriteService
     */
    private $favoriteService;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * AppExtension constructor.
     * @param ContainerInterface $container
     * @param UploaderHelper $uploaderHelper
     * @param SettingsService $settingsService
     * @param TranslatorInterface $translator
     * @param FavoriteService $favoriteService
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(ContainerInterface $container, UploaderHelper $uploaderHelper, SettingsService $settingsService, TranslatorInterface $translator, FavoriteService $favoriteService, TokenStorageInterface $tokenStorage)
    {
        $this->container = $container;
        $this->uploaderHelper = $uploaderHelper;
        $this->settingsService = $settingsService;
        $this->translator = $translator;
        $this->favoriteService = $favoriteService;
        $this->tokenStorage = $tokenStorage;
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
        ];
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getUrl', [$this, 'getUrl']),
            new TwigFunction('uploaded_location_asset', [$this, 'getUploadedLocationAssetPath']),
            new TwigFunction('aboutStore', [$this, 'aboutStore'])
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
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return float|int|string
     */
    public function formatPrice($number, $decimals = 2, $decPoint = '.', $thousandsSep = ',')
    {
        $price = $number / 100;
        $price = number_format($price, $decimals, $decPoint, $thousandsSep);

        return $price;
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
     * @throws Exception
     * @return string
     */
    public function getUrl()
    {
        return getenv('APP_URL');
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
            case 'email': return $this->settingsService->getEmail();
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
}
