<?php

namespace App\Controller\AdminApi;

use App\Model\APIResponse;
use App\Entity\Order\Order;
use App\Service\OrderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;

/**
 * OrderController
 * @Route("/orders")
 */
class OrderController extends AbstractFOSRestController
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * DashboardController constructor.
     * @param OrderRepository $orderRepository
     * @param OrderService $orderService
     */
    public function __construct(
        OrderRepository $orderRepository,
        OrderService $orderService
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="admin_api_orders_show",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $id = $request->get('id');
        /** @var Order $order */
        $order = $this->orderRepository->find($id);

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeOrder($order, true)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

}
