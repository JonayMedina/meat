<?php

namespace App\Controller\AdminApi;

use Carbon\Carbon;
use App\Service\DashboardService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;

/**
 * RatingController
 * @Route("/order-rating")
 */
class RatingController extends AbstractFOSRestController
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var DashboardService
     */
    private $dashboardService;

    /**
     * RatingController constructor.
     * @param OrderRepository $orderRepository
     * @param DashboardService $dashboardService
     */
    public function __construct(
        OrderRepository $orderRepository,
        DashboardService $dashboardService
    ) {
        $this->orderRepository = $orderRepository;
        $this->dashboardService = $dashboardService;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="api_rating_index",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function indexAction(Request $request): Response
    {
        $statusCode = Response::HTTP_OK;

        $startsAt = $request->get('startsAt', date('Y-m-d', strtotime(DashboardService::START_DATE_MODIFIER, strtotime('now'))));
        $endsAt = $request->get('endsAt', date('Y-m-d'));

        $dashboard = $this->dashboardService
            ->setStartDate($startsAt)
            ->setEndDate($endsAt)
            ->calculateAverageRating();

        $response = [
            'rating' => [
                'rating' => $dashboard->getAverageRating(),
                'order_counter' => $dashboard->getAverageRatingCounter(),
            ],
            'startsAt' => $startsAt,
            'endsAt' => $endsAt,
        ];

        $view = $this->view($response, $statusCode);
        return $this->handleView($view);
    }
}
