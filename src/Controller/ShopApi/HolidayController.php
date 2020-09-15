<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use App\Repository\FAQRepository;
use App\Repository\HolidayRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * HolidayController
 * @Route("/holidays")
 */
class HolidayController extends AbstractFOSRestController
{
    /**
     * @var HolidayRepository
     */
    private $repository;

    /**
     * HolidayController constructor.
     * @param HolidayRepository $repository
     */
    public function __construct(HolidayRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_holidays",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $statusCode = Response::HTTP_OK;
        $holidays = $this->repository->createQueryBuilder('holiday')
            ->getQuery()
            ->getResult();

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Holiday list', $holidays);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
