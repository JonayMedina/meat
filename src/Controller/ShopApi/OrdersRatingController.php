<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use App\Entity\Order\Order;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;

/**
 * OrdersRatingController
 * @Route("/orders/{tokenValue}")
 */
class OrdersRatingController extends AbstractFOSRestController
{
    /**
     * @var OrderRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * OrdersRating constructor.
     * @param OrderRepository $repository
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(OrderRepository $repository, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(
     *     "/rating.{_format}",
     *     name="shop_api_get_order_rating",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function getRatingAction(Request $request)
    {
        $tokenValue = $request->get('tokenValue');
        $order = $this->getOrder($tokenValue);

        if (!$order instanceof Order) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $recordset = [];

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Not found.', $recordset);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_OK;

        $recordset = [
            'token' => $order->getTokenValue(),
            'rating' => $order->getRating(),
            'comment' => $order->getRatingComment(),
        ];

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Order rating.', $recordset);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/rating.{_format}",
     *     name="shop_api_post_order_rating",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function publishRatingAction(Request $request)
    {
        $tokenValue = $request->get('tokenValue');
        $order = $this->getOrder($tokenValue);

        if (!$order instanceof Order) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $recordset = [];

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Not found.', $recordset);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $rating = $request->get('rating');
        $comment = $request->get('comment');

        if (!$this->notValidParameter($rating)) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $recordset = [];

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Rating is not valid.', $recordset);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $order->setRating($rating);
        $order->setRatingComment($comment);

        try {
            $this->entityManager->flush();

            $statusCode = Response::HTTP_OK;
            $recordset = [
                'token' => $order->getTokenValue(),
                'rating' => $order->getRating(),
                'comment' => $order->getRatingComment(),
            ];

            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Updated.', $recordset);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);

        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $recordset = [];

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $exception->getMessage(), $recordset);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * Return order.
     * @param $tokenValue
     * @return Order|null
     */
    private function getOrder($tokenValue): ?Order
    {
        try {
            /** @var ShopUser $user */
            $user = $this->getUser();

            return $this->repository->createQueryBuilder('o')
                ->andWhere('o.tokenValue = :tokenValue')
                ->andWhere('o.customer = :customer')
                ->setParameter('tokenValue', $tokenValue)
                ->setParameter('customer', $user->getCustomer())
                ->getQuery()
                ->getOneOrNullResult();

        } catch (NonUniqueResultException $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }

    /**
     * @param $rating
     * @return bool
     */
    private function notValidParameter($rating): ?bool
    {
        if (!is_numeric($rating)) {
            return false;
        }

        if ($rating < Order::MIN_RATING || $rating > Order::MAX_RATING) {
            return false;
        }

        return true;
    }
}
