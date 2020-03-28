<?php

namespace App\Twig;

use Exception;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Service\UploaderHelper;
use Twig\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    /**
     * AppExtension constructor.
     * @param ContainerInterface $container
     * @param UploaderHelper $uploaderHelper
     */
    public function __construct(ContainerInterface $container, UploaderHelper $uploaderHelper)
    {
        $this->container = $container;
        $this->uploaderHelper = $uploaderHelper;
    }

    /**
     * @return array|TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('price', [$this, 'formatPrice']),
            new TwigFilter('base64', [$this, 'imageToBase64'])
        ];
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getUrl', [$this, 'getUrl']),
            new TwigFunction('uploaded_location_asset', [$this, 'getUploadedLocationAssetPath'])
        ];
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
}
