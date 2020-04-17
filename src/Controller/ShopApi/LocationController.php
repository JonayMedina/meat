<?php

namespace App\Controller\ShopApi;

use App\Entity\Location;
use App\Model\APIResponse;
use App\Repository\FAQRepository;
use App\Repository\LocationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * LocationController
 * @Route("/locations")
 */
class LocationController extends AbstractFOSRestController
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
     * DashboardController constructor.
     * @param LocationRepository $repository
     * @param $uploadedAssetsBaseUrl
     */
    public function __construct(LocationRepository $repository, $uploadedAssetsBaseUrl)
    {
        $this->repository = $repository;
        $this->uploadedAssetsBaseUrl = $uploadedAssetsBaseUrl;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_locations",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $statusCode = Response::HTTP_OK;
        /** @var Location[] $locations */
        $locations = $this->repository->createQueryBuilder('location')
            ->getQuery()
            ->getResult();

        foreach ($locations as $location) {
            $location->photoUrl = $this->uploadedAssetsBaseUrl.'/'.$location->getFilePath();
        }

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Location list', $locations);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
