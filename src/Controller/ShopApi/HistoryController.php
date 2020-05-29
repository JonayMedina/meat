<?php

namespace App\Controller\ShopApi;

use App\Entity\Customer\Customer;
use App\Entity\Order\Order;
use App\Model\APIResponse;
use App\Service\OrderService;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use App\Service\HistoryService;
use App\Pagination\PaginationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class HistoryController
 * @Route("/history")
 */
class HistoryController extends AbstractFOSRestController
{
    const ITEMS_PER_PAGE = 10;

    /** @var HistoryService */
    private $historyService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var OrderRepository
     */
    private $repository;

    /**
     * ExampleController constructor.
     * @param HistoryService $historyService
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param OrderService $orderService
     * @param OrderRepository $repository
     */
    public function __construct(
        HistoryService $historyService,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        OrderService $orderService,
        OrderRepository $repository
    )
    {
        $this->historyService = $historyService;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->orderService = $orderService;
        $this->repository = $repository;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_history",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @param PaginationFactory $paginationFactory
     * @return Response
     */
    public function historyAction(Request $request, PaginationFactory $paginationFactory)
    {
        $statusCode = Response::HTTP_OK;

        /** @var ShopUser $user */
        $user = $this->getUser();
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', self::ITEMS_PER_PAGE);

        $queryBuilder = $this->historyService->getOrderHistoryQuery($user);

        $list = [];
        $paginatedCollection = $paginationFactory->createCollection($queryBuilder, '', $page, $limit, 'shop_api_history', [], 'Order history list.', $statusCode, 'info');

        foreach ($paginatedCollection->recordset as $item) {
            /** @var Order $item */
            $list[] = $this->orderService->serializeOrder($item);
        }

        $paginatedCollection->recordset = $list;

        $view = $this->view($paginatedCollection, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/reorder/{id}.{_format}",
     *     name="shop_api_history_reorder",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function reorder(Request $request): Response
    {
        /** @var ShopUser $user */
        $user = $this->getUser();
        /** @var Customer $customer */
        $customer = $user->getCustomer();

        $id = $request->get('id');
        $order = $this->repository->find($id);

        if (!$order instanceof Order) {
            throw new NotFoundHttpException('Order not found');
        }

        if ($order->getCustomer() !== $customer) {
            throw new NotFoundHttpException('Customer has no access to this order.');
        }

        $newOrder = $this->historyService->reorder($order, $customer);

        $statusCode = Response::HTTP_OK;
        $serialized = $this->orderService->serializeOrder($newOrder);
        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'New order created.', $serialized);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
