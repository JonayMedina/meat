<?php

namespace App\Service;

use App\Message\Sync;
use App\Entity\Order\Order;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminSyncService
{
    /**
     * @var MessageBusInterface $bus
     */
    private $bus;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * WelcomeCommand constructor.
     * @param MessageBusInterface $bus
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(MessageBusInterface $bus, UrlGeneratorInterface $urlGenerator)
    {
        $this->bus = $bus;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Start sync process for order after checkout completed.
     * @param Order $order
     */
    public function syncOrderAfterCheckoutCompleted(Order $order): void
    {
        $url = $this->urlGenerator->generate('admin_api_orders_show', [
            'id' => $order->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $metadata = [
            'id' => $order->getId(),
            'token' => $order->getTokenValue(),
            'total' => $order->getTotal()/100,
            'total_items' => $order->getTotalQuantity(),
            'number' => $order->getNumber(),
            'estimated_delivery_date' => $order->getEstimatedDeliveryDate(),
            'preferred_delivery_time' => $order->getPreferredDeliveryTime(),
        ];

        $this->bus->dispatch(new Sync(
            Sync::TYPE_ORDER_CHECKOUT_COMPLETED,
            Sync::MODEL_ORDER,
            $order->getId(),
            $url,
            $metadata
        ));
    }
}
