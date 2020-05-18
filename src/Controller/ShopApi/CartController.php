<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;

/**
 * CartController
 * @Route("/sync-carts")
 */
class CartController extends AbstractFOSRestController
{
    /**
     * @var OrderRepository
     */
    private $repository;

    /**
     * CartController constructor.
     * @param OrderRepository $repository
     */
    public function __construct(OrderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_get_carts",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $list = [];
        $statusCode = Response::HTTP_OK;

        /** @var ShopUser $user */
        $user = $this->getUser();

        /** @var Order[] $orders */
        $orders = $this->repository
            ->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.tokenValue IS NOT NULL')
            ->andWhere('o.state = :state')
            ->setParameter('customer', $user->getCustomer())
            ->setParameter('state', OrderInterface::STATE_CART)
            ->getQuery()
            ->getResult();

        foreach ($orders as $order) {
            $list[] = [
                'id' => $order->getId(),
                'number' => $order->getNumber(),
                'token_value' => $order->getTokenValue(),
            ];
        }

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Cart list', $list);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
