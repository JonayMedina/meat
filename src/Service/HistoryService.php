<?php

namespace App\Service;

use App\Entity\Order\Order;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Bundle\CoreBundle\Storage\CartSessionStorage;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Sylius\CustomerReorderPlugin\Reorder\ReordererInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class HistoryService
 * @package App\Service
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class HistoryService
{
    const HISTORY_LIMIT = 5;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /***
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var ReordererInterface
     */
    private $reorderer;

    /**
     * @var CartSessionStorage
     */
    private $cartSessionStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * HistoryService constructor.
     * @param OrderRepository $orderRepository
     * @param EntityManagerInterface $entityManager
     * @param ChannelContextInterface $channelContext
     * @param ReordererInterface $reorderService
     * @param CartSessionStorage $cartSessionStorage
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager,
        ChannelContextInterface $channelContext,
        ReordererInterface $reorderService,
        CartSessionStorage $cartSessionStorage,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->entityManager = $entityManager;
        $this->channelContext = $channelContext;
        $this->reorderer = $reorderService;
        $this->cartSessionStorage = $cartSessionStorage;
        $this->logger = $logger;
    }

    /**
     * @param ShopUser $user
     * @return Order[]
     */
    public function getOrderHistory(ShopUser $user)
    {
        /** @var Order[] $history */
        $history = $this->getOrderHistoryQuery($user)
            ->getQuery()
            ->getResult();

        return $history;
    }

    /**
     * @param ShopUser $user
     * @return int|mixed|string
     * @throws NonUniqueResultException
     */
    public function getLastOrder(ShopUser $user) {
        /** @var Order $order */
        $order = $this->getOrderHistoryQuery($user, 1)
            ->getQuery()
            ->getOneOrNullResult();

        return $order;
    }

    /**
     * @param Order $order
     * @param Customer $customer
     * @return Order|null
     */
    public function reorder(Order $order, Customer $customer): ?Order
    {
        if ($order->getCustomer() !== $customer) {
            throw new AccessDeniedHttpException('Customer has no access to this order.');
        }

        $reorder = null;
        /** @var ChannelInterface $channel */
        $channel = $this->channelContext->getChannel();

        if (!$order->getShippingAddress() instanceof Address) {
            throw new BadRequestHttpException('Esta orden no tiene una dirección de envío');
        }

        if (!$order->getBillingAddress() instanceof Address) {
            throw new BadRequestHttpException('Esta orden no tiene una dirección de facturación');
        }

        try {
            /** @var Order $reorder */
            $reorder = $this->reorderer->reorder($order, $channel, $customer);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }

        $this->cartSessionStorage->setForChannel($channel, $reorder);

        return $reorder;
    }

    /**
     * @param ShopUser $user
     * @param null|int $limit
     * @return QueryBuilder
     */
    public function getOrderHistoryQuery(ShopUser $user, $limit = null): QueryBuilder
    {
        $customer = $user->getCustomer();

        return $this->orderRepository
            ->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.paymentState = :paymentState')
            ->setParameter('customer', $customer)
            ->setParameter('paymentState', OrderPaymentStates::STATE_PAID)
            ->orderBy('o.estimatedDeliveryDate', 'DESC')
            ->addOrderBy('o.id', 'DESC')
            ->setMaxResults($limit ? $limit : self::HISTORY_LIMIT);
    }
}
