<?php

namespace App\Twig;

use App\Service\SettingsService;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig_Function;

/**
 * Class AppExtension
 * @package App\Twig
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class AppExtension extends AbstractExtension
{
    /** @var ContainerInterface $container */
    private $container;

    /** @var SettingsService $settingsService */
    private $settingsService;

    public function __construct(ContainerInterface $serviceContainer, SettingsService $settingsService)
    {
        $this->container = $serviceContainer;
        $this->settingsService = $settingsService;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
            new TwigFilter('base64', [$this, 'imageToBase64'])
        ];
    }

    /**
     * @return array|Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getUrl', [$this, 'getUrl']),
            new TwigFunction('aboutStore', [$this, 'AboutStore'])
        ];
    }

    public function formatPrice($number, $decimals = 2, $decPoint = '.', $thousandsSep = ',')
    {
        $price = $number / 100;
        $price = number_format($price, $decimals, $decPoint, $thousandsSep);

        return $price;
    }

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
    public function aboutStore($option = 'author') {
        switch ($option) {
            case 'facebook': return $this->settingsService->getFacebookUrl();
            case 'instagram': return $this->settingsService->getInstagramUrl();
            case 'twitter': return $this->settingsService->getTwitterUrl();
            case 'pinterest': return $this->settingsService->getPinterestUrl();
            case 'app-store': return $this->settingsService->getAppStoreUrl();
            case 'play-store': return $this->settingsService->getPlayStoreUrl();
            case 'phrase': return $this->settingsService->getPhrase();
            case 'about-us': return $this->settingsService->getAboutUs();
            case 'delivery-hours': return $this->settingsService->getDeliveryHours();
            case 'show-search': return $this->settingsService->getShowProductSearchBox();
            case 'days-to-choose': return $this->settingsService->getDaysToChooseInAdvanceToPurchase();
            case 'first-purchase-ms': return $this->settingsService->getFirstPurchaseMessage();
            case 'new-address-ms': return $this->settingsService->getNewAddressMessage();
            case 'max-purchase': return $this->settingsService->getMaximumPurchaseValue();
            case 'min-purchase': return $this->settingsService->getMinimumPurchaseValue();
            default:
                return $this->settingsService->getAuthor();
        }
    }
}
