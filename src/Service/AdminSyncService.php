<?php

namespace App\Service;

use App\Message\Sync;
use App\Entity\Order\Order;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
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
    public function __construct(
        MessageBusInterface $bus,
        UrlGeneratorInterface $urlGenerator
    ) {
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
            'general_status' => $order->getStatus(),
            'checkout_state' => $order->getCheckoutState(),
            'payment_state' => $order->getPaymentState(),
            'shipping_state' => $order->getShippingState(),
        ];

        $this->bus->dispatch(new Sync(
            Sync::TYPE_ORDER_CHECKOUT_COMPLETED,
            Sync::MODEL_ORDER,
            $order->getId(),
            $url,
            $metadata
        ));
    }

    /**
     * @param Address $address
     */
    public function syncAddressAfterCreation(Address $address): void
    {
        if ($address->getStatus() == Address::STATUS_TO_CLONE || $address->getParent() instanceof Address) {
            return;
        }

        if ($address->getType() == Address::TYPE_BILLING) {
            return;
        }

        $url = $this->urlGenerator->generate('admin_api_address_show', [
            'id' => $address->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $metadata = [];

        $this->bus->dispatch(new Sync(
            Sync::TYPE_PERSIST,
            Sync::MODEL_ADDRESS,
            $address->getId(),
            $url,
            $metadata
        ));
    }

    /**
     * @param Order $order
     */
    public function syncOrderAfterRating(Order $order): void
    {
        $url = $this->urlGenerator->generate('admin_api_orders_show', [
            'id' => $order->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $metadata = [
            'id' => $order->getId(),
            'token' => $order->getTokenValue(),
            'number' => $order->getNumber(),
            'rating' => [
                'rating' => $order->getRating(),
                'comment' => $order->getRatingComment(),
            ]
        ];

        $this->bus->dispatch(new Sync(
            Sync::TYPE_ORDER_RATED,
            Sync::MODEL_ORDER,
            $order->getId(),
            $url,
            $metadata
        ));
    }

    /**
     * @param Customer $customer
     */
    public function syncCustomerAfterCreation(Customer $customer): void
    {
        $url = $this->urlGenerator->generate('admin_api_customers_show', [
            'id' => $customer->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $metadata = [
            'id' => $customer->getId(),
            'first_name' => $customer->getFirstName(),
            'last_name' => $customer->getLastName(),
            'email' => $customer->getEmail(),
            'gender' => $customer->getGender(),
        ];

        $this->bus->dispatch(new Sync(
            Sync::TYPE_PERSIST,
            Sync::MODEL_CUSTOMER,
            $customer->getId(),
            $url,
            $metadata
        ));
    }
}
