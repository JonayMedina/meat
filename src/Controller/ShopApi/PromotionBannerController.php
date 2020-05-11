<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use App\Entity\PromotionBanner;
use App\Repository\LocationRepository;
use App\Repository\PromotionBannerRepository;
use App\Service\UploaderHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * PromotionBannerController
 * @Route("/promotion-banners")
 */
class PromotionBannerController extends AbstractFOSRestController
{
    /**
     * @var LocationRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $uploadedAssetsBaseUrl;

    /**
     * PromotionBannerController constructor.
     * @param PromotionBannerRepository $repository
     * @param $uploadedAssetsBaseUrl
     */
    public function __construct(PromotionBannerRepository $repository, $uploadedAssetsBaseUrl)
    {
        $this->repository = $repository;
        $this->uploadedAssetsBaseUrl = $uploadedAssetsBaseUrl;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_promotion_banners",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $statusCode = Response::HTTP_OK;
        $now = date('Y-m-d H:i:s');

        /** @var PromotionBanner[] $banners */
        $banners = $this->repository->createQueryBuilder('promotion_banner')
            ->andWhere('promotion_banner.startDate <= :now')
            ->andWhere('promotion_banner.endDate >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        foreach ($banners as $banner) {

            if ($banner->getPhotoWeb()) {
                $banner->bannerWebUrl = $this->uploadedAssetsBaseUrl.'/'.UploaderHelper::BANNER_PHOTO_IMAGE.'/'.$banner->getPhotoWeb();
            }

            if ($banner->getPhotoTablet()) {
                $banner->bannerTabletUrl = $this->uploadedAssetsBaseUrl.'/'.UploaderHelper::BANNER_PHOTO_IMAGE.'/'.$banner->getPhotoTablet();
            }

            if ($banner->getPhotoMobile()) {
                $banner->bannerMobileUrl = $this->uploadedAssetsBaseUrl.'/'.UploaderHelper::BANNER_PHOTO_IMAGE.'/'.$banner->getPhotoMobile();
            }

            if ($banner->getPhotoApp()) {
                $banner->bannerAppUrl = $this->uploadedAssetsBaseUrl.'/'.UploaderHelper::BANNER_PHOTO_IMAGE.'/'.$banner->getPhotoApp();
            }

            if ($banner->getProductVariant()) {
                $variant = $banner->getProductVariant();
                $product = $variant->getProduct();

                $banner->product = [
                    'name' => $product->getName(),
                    'code' => $product->getCode(),
                    'slug' => $product->getSlug(),
                ];
            }
        }

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Location list', $banners);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
