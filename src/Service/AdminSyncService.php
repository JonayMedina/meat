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
     * @var string $apiUrl
     */
    private $apiUrl;

    /**
     * WelcomeCommand constructor.
     * @param MessageBusInterface $bus
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $apiUrl
     */
    public function __construct(
        MessageBusInterface $bus,
        UrlGeneratorInterface $urlGenerator,
        $apiUrl
    ) {
        $this->bus = $bus;
        $this->urlGenerator = $urlGenerator;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Start sync process for order after checkout completed.
     * @param Order $order
     */
    public function syncOrderAfterCheckoutCompleted(Order $order): void
    {
        $url = $this->apiUrl . '/v1/orders/' . $order->getId();
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
     * @param string $type
     */
    public function syncAddressAfterCreation(Address $address, $type = Sync::TYPE_PERSIST): void
    {
        if ($address->getCustomer() == null || $address->getParent() instanceof Address) {
            return;
        }

        if ($address->getType() == Address::TYPE_BILLING) {
            return;
        }

        $url = $this->apiUrl . '/v1/addresses/' . $address->getId();

        $metadata = [
            'validated' => ($address->getStatus() == Address::STATUS_VALIDATED)
        ];

        $this->bus->dispatch(new Sync(
            $type,
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
        $url = $this->apiUrl . '/v1/orders/' . $order->getId();

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
     * @param string $type
     */
    public function syncCustomerAfterCreation(Customer $customer, $type = Sync::TYPE_PERSIST): void
    {
        $url = $this->apiUrl . '/v1/customers/' . $customer->getId();

        $metadata = [
            'id' => $customer->getId(),
            'first_name' => $customer->getFirstName(),
            'last_name' => $customer->getLastName(),
            'email' => $customer->getEmail(),
            'gender' => $customer->getGender(),
        ];

        $this->bus->dispatch(new Sync(
            $type,
            Sync::MODEL_CUSTOMER,
            $customer->getId(),
            $url,
            $metadata
        ));
    }
}
