<?php

namespace App\Service;

use Carbon\Carbon;
use App\Entity\Holiday;
use App\Entity\AboutStore;
use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use App\Entity\Order\OrderItem;
use App\Entity\Payment\Payment;
use App\Entity\Order\Adjustment;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use App\Repository\HolidayRepository;
use App\Entity\Product\ProductVariant;
use Psr\Cache\InvalidArgumentException;
use App\Repository\AboutStoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Promotion\PromotionCoupon;
use Doctrine\ORM\NonUniqueResultException;
use Carbon\Exceptions\InvalidFormatException;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Order\Processor\CompositeOrderProcessor;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sylius\Component\Core\Cart\Modifier\LimitingOrderItemQuantityModifier;

class OrderService
{
    /** Order will be sent today during day */
    const TODAY = 'TODAY';

    /** Order will be sent next available day at morning */
    const NEXT_DAY_MORNING = 'NEXT_DAY_MORNING';

    /** Order will be sent next available day afternoon */
    const NEXT_DAY_AFTERNOON = 'NEXT_DAY_AFTERNOON';

    /**
     * @var HolidayRepository
     */
    private $holidayRepository;

    /**
     * @var AboutStoreRepository
     */
    private $aboutStoreRepository;

    /**
     * @var OrderRepository
     */
    private $repository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LimitingOrderItemQuantityModifier
     */
    private $itemQuantityModifier;

    /**
     * @var CompositeOrderProcessor
     */
    private $compositeOrderProcessor;

    /**
     * @var CartItemFactoryInterface
     */
    private $cartItemFactory;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var string Current local store timezone.
     */
    private $timezone = 'America/Guatemala';

    /**
     * OrderService constructor.
     * @param HolidayRepository $holidayRepository
     * @param AboutStoreRepository $aboutStoreRepository
     * @param OrderRepository $repository
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param LimitingOrderItemQuantityModifier $itemQuantityModifier
     * @param CompositeOrderProcessor $compositeOrderProcessor
     * @param CartItemFactoryInterface $cartItemFactory
     * @param ChannelContextInterface $channelContext
     */
    public function __construct(
        HolidayRepository $holidayRepository,
        AboutStoreRepository $aboutStoreRepository,
        OrderRepository $repository,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        LimitingOrderItemQuantityModifier $itemQuantityModifier,
        CompositeOrderProcessor $compositeOrderProcessor,
        CartItemFactoryInterface $cartItemFactory,
        ChannelContextInterface $channelContext
    ) {
        $this->holidayRepository = $holidayRepository;
        $this->aboutStoreRepository = $aboutStoreRepository;
        $this->repository = $repository;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->itemQuantityModifier = $itemQuantityModifier;
        $this->compositeOrderProcessor = $compositeOrderProcessor;
        $this->cartItemFactory = $cartItemFactory;
        $this->channelContext = $channelContext;
    }

    /**
     * Find next available date for order.
     * @param string $preferredDeliveryDate
     * @param string $scheduledDeliveryDate
     * @return Carbon
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     */
    public function getNextAvailableDay($preferredDeliveryDate = "", $scheduledDeliveryDate = "")
    {
        $today = Carbon::now($this->timezone);
        $hour = (int)$today->format('G');
        $isOrderForToday = ($today->format('Y-m-d') == $scheduledDeliveryDate);
        $immutableToday = clone $today;

// TODO: Remove
//        $today = new Carbon();
//        $today->setTime(mt_rand(0, 23), mt_rand(0, 59));

        $nextAvailability = self::TODAY;

        if ($hour >= 0 && $hour <= 6) {
            $nextAvailability = self::TODAY;
        }

        if ($hour >= 6 && $hour < 15) {
            $nextAvailability = self::NEXT_DAY_MORNING;
        }

        if ($hour >= 15 && $hour <= 23) {
            $nextAvailability = self::NEXT_DAY_AFTERNOON;
        }

// TODO: Remove
//        dump($today->format('H:i'), $nextAvailability);

        $aboutStore = $this->aboutStoreRepository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            throw new NotFoundHttpException('Settings are missing.');
        }

        try {
            $scheduledDeliveryDate = Carbon::parse($scheduledDeliveryDate, $this->timezone);
        } catch (InvalidFormatException $exception) {
            throw new BadRequestHttpException('Settings are missing.');
        }

        if ($isOrderForToday) {
            $today->setHour(0)->setMinutes(0)->setSeconds(0)->setMicroseconds(0);
        }

        if ($scheduledDeliveryDate->lessThan($today)) {
            throw new BadRequestHttpException('La fecha elegida para el envío no es valida, seleccione una fecha futura.');
        }

        $hours = [];
        $deliveryHours = $aboutStore->getDeliveryHours();

        foreach ($deliveryHours as $deliveryHour) {
            $label = $deliveryHour['name'];
            $hours[$label] = $deliveryHour;
        }

        $timeRange = $hours[$preferredDeliveryDate] ?? null;

        if ($isOrderForToday) {
            $timeRangeParts = explode(':', $timeRange['end']);
            $timeRangeHour = (int)$timeRangeParts[0];
            $timeRangeMinutes = (int)$timeRangeParts[1];

            if ((int)$immutableToday->format('H') >= $timeRangeHour && (int)$immutableToday->format('i') >= $timeRangeMinutes) {
                throw new BadRequestHttpException('La fecha elegida para el envío no es valida, seleccione una fecha futura.');
            }
        }

        $daysDifference = $this->diffInDays($today, $scheduledDeliveryDate);

        if ((bool)$aboutStore->getDaysToChooseInAdvanceToPurchase() && ($daysDifference > $aboutStore->getDaysToChooseInAdvanceToPurchase())) {
            throw new BadRequestHttpException('El día de entrega seleccionado sobrepasa el rango permitido.');
        }

        $nextAvailableDay = $this->findValidDeliverDate($scheduledDeliveryDate, null, $nextAvailability);

        return $this->setTimeToAvailableDay($nextAvailableDay, $preferredDeliveryDate, $nextAvailability);
    }

    /**
     * Merge available carts and return merged cart.
     * @param ShopUser|null $user
     * @return Order
     */
    public function mergeCarts(ShopUser $user = null): Order
    {
        if (!$user instanceof ShopUser) {
            throw new NotFoundHttpException('Esta API necesita autenticación por JWT');
        }

        $orders = $this->getOrders($user);
        /** @var ProductVariant[] $variants */
        $variants = [];
        $mainOrder = $orders[0] ?? null;

        foreach ($orders as $index => $order) {
            /** No sumar los productos del carrito que no se eliminará... */
            if ($index > 0) {
                /** @var OrderItem $orderItem */
                foreach ($order->getItems() as $orderItem) {
                    $variant = $orderItem->getVariant();
                    $variants[$variant->getId()]['variant'] = $variant;

                    /** calculate quantity max always... */
                    if ($orderItem->getQuantity() > ($variants[$variant->getId()]['quantity'] ?? 0)) {
                        $variants[$variant->getId()]['quantity'] = $orderItem->getQuantity();
                    }
                }
            }
        }

        if (!$mainOrder instanceof Order) {
            throw new NotFoundHttpException($this->translator->trans('app.api.cart.no_carts_available_for_current_user'));
        }

        foreach ($variants as $variant) {
            /** @var OrderItem $orderItem */
            $orderItem = $this->cartItemFactory->createNew();
            $orderItem->setVariant($variant['variant']);
            $this->itemQuantityModifier->modify($orderItem, $variant['quantity']);

            $this->entityManager->persist($orderItem);

            $mainOrder->addItem($orderItem);
            $this->compositeOrderProcessor->process($mainOrder);
        }

        $this->repository->add($mainOrder);

        /** Remove previous carts */
        foreach ($orders as $index => $order) {
            if ($index > 0) {
                $this->repository->remove($order);
            }
        }

        try {
            $this->entityManager->flush();

            return $mainOrder;
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    /**
     * Return serialized order object.
     * @param Order|null $order
     * @param bool $details
     * @return array
     */
    public function serializeOrder(Order $order, $details = false)
    {
        $payments = [];
        $items = [];
        $coupon = null;
        $adjustments = [];
        $customer = null;
        $rating = [];
        $shipments = [];

        if ($details) {
            foreach ($order->getPayments() as $payment) {
                $payments[] = $this->serializePayment($payment);
            }

            foreach ($order->getItems() as $orderItem) {
                $items[] = $this->serializeOrderItem($orderItem);
            }

            foreach ($order->getAdjustments() as $adjustment) {
                $adjustments[] = $this->serializeAdjustment($adjustment);
            }

            if ($order->getPromotionCoupon()) {
                /** @var PromotionCoupon $couponOrder */
                $couponOrder = $order->getPromotionCoupon();
                $coupon = $this->serializeCoupon($couponOrder);
            }

            foreach ($order->getShipments() as $shipment) {
                $shipments[] = $this->serializeShipment($shipment);
            }

            $rating = $this->serializeRating($order);
            $customer = $this->serializeCustomer($order->getCustomer());
        }

        /** @var Address $shippingAddress */
        $shippingAddress = null;

        if ($order->getShippingAddress() instanceof Address) {
            $shippingAddress = $order->getShippingAddress();
        }

        if ($order->getShippingAddress() instanceof Address && $order->getShippingAddress()->getParent() instanceof Address) {
            $shippingAddress = $order->getShippingAddress()->getParent();
        }

        /** @var Address $billingAddress */
        $billingAddress = $order->getBillingAddress();

        $response = [
            'id' => $order->getId(),
            'number' => $order->getNumber(),
            'total_quantity' => $order->getTotalQuantity(),
            'token_value' => $order->getTokenValue(),
            'created_at' => $order->getCreatedAt()->format('c'),
            'checkout_completed_at' => $order->getCheckoutCompletedAt() ? $order->getCheckoutCompletedAt()->format('c') : null,
            'estimated_delivery_date' => $order->getEstimatedDeliveryDate(),
            'preferred_delivery_time' => $order->getPreferredDeliveryTime(),
            'status' => $order->getStatus(),
            'order_state' => $order->getState(),
            'checkout_state' => $order->getCheckoutState(),
            'payment_state' => $order->getPaymentState(),
            'shipping_state' => $order->getShippingState(),
            'shipping_address' => $this->serializeAddress($shippingAddress),
            'billing_address' => $this->serializeAddress($billingAddress),
        ];

        if ($details) {
            $response['items_total'] = $order->getItemsTotal()/100;
            $response['adjustments_total'] = $order->getAdjustmentsTotal()/100;
            $response['total'] = $order->getTotal()/100;
            $response['payments'] = $payments;
            $response['shipments'] = $shipments;
            $response['items'] = $items;
            $response['adjustments'] = $adjustments;
            $response['coupon'] = $coupon;
            $response['customer'] = $customer;
            $response['rating'] = $rating;
        }

        return $response;
    }

    /**
     * @param Carbon $date
     * @return bool
     * @throws NonUniqueResultException
     */
    private function isHoliday(Carbon $date): bool
    {
        $holiday = $this->holidayRepository
            ->createQueryBuilder('holiday')
            ->andWhere('holiday.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        return ($holiday instanceof Holiday);
    }

    /**
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    private function diffInDays(Carbon $start, Carbon $end)
    {
        return $start->diffInDays($end);
    }

    /**
     * @param Carbon $scheduledDeliveryDate
     * @param string|null $start
     * @param null $nextAvailability
     * @return Carbon
     * @throws NonUniqueResultException
     */
    private function findValidDeliverDate(Carbon $scheduledDeliveryDate, $start = null, $nextAvailability = null): Carbon
    {
        $isAvailable = false;
        $start = $start ? $start : 'now';
        $today = Carbon::parse($start, $this->timezone);

        // TODO: Tal vez recibir el today...

        while (!$isAvailable) {
            $todayAtTwelve = $today->copy()->setHours(12)->setMinutes(00)->setSeconds(00);
            if ($this->isHoliday($today) || $today->isAfter($todayAtTwelve) || $today->isSunday()) {
                $this->addOneDay($today);
            } else {
                $isAvailable = true;
            }
        }

        $nextAvailableDay = $today;

        $scheduledDeliveryDateAtTwelve = $scheduledDeliveryDate->copy()->setHours(12)->setMinutes(00)->setSeconds(00);
        if ($scheduledDeliveryDate->isAfter($scheduledDeliveryDateAtTwelve)) {
            return $this->findValidDeliverDate($nextAvailableDay, $scheduledDeliveryDate->format('Y-m-d H:i:s'));
        }

        $scheduledDeliveryDate = ($scheduledDeliveryDate->isAfter($nextAvailableDay)) ? $scheduledDeliveryDate : $nextAvailableDay;

        return $scheduledDeliveryDate;
    }

    /**
     * Reset time and add one day.
     * @param Carbon $date
     * @return Carbon
     */
    private function addOneDay(Carbon $date): Carbon
    {
        /** Reset datetime to 00:00:00 */
        return $date
            ->setHours(0)
            ->setMinutes(00)
            ->setSeconds(00)
            ->addDays(1);
    }

    /**
     * @param Carbon $nextAvailableDay
     * @param $preferredDeliveryDate
     * @param $nextAvailability
     * @return Carbon
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     */
    private function setTimeToAvailableDay(Carbon $nextAvailableDay, $preferredDeliveryDate, $nextAvailability): Carbon
    {
        $aboutStore = $this->aboutStoreRepository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            throw new NotFoundHttpException('Settings are missing.');
        }

        $hours = [];
        $deliveryHours = $aboutStore->getDeliveryHours();

        foreach ($deliveryHours as $deliveryHour) {
            $label = $deliveryHour['name'];
            $hours[$label] = $deliveryHour;
        }

        $timeRange = $hours[$preferredDeliveryDate] ?? null;

        if (!empty($timeRange)) {
            $end = $timeRange['end'];
            $chunks = explode(':', $end);

            return $nextAvailableDay
                ->setHours($chunks[0])
                ->setMinutes($chunks[1])
                ->setSeconds(00);
        }

        if ($nextAvailableDay->isSaturday()) {
            return $nextAvailableDay
                ->setHours(13)
                ->setMinutes(00)
                ->setSeconds(00);
        }

        return $nextAvailableDay
            ->setHours(17)
            ->setMinutes(00)
            ->setSeconds(00);
    }


    /**
     * Return customer's orders.
     * @param ShopUser $user
     * @return Order[]
     */
    private function getOrders(ShopUser $user)
    {
        /** @var Order[] $orders */
        $orders = $this->repository
            ->createQueryBuilder('o')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.tokenValue IS NOT NULL')
            ->andWhere('o.state = :state')
            ->andWhere('o.paymentState NOT IN (:paymentStatuses)')
            ->setParameter('customer', $user->getCustomer())
            ->setParameter('state', OrderInterface::STATE_CART)
            ->setParameter('paymentStatuses', [
                OrderPaymentStates::STATE_PAID,
                OrderPaymentStates::STATE_CANCELLED,
                OrderPaymentStates::STATE_REFUNDED,
                OrderPaymentStates::STATE_PARTIALLY_REFUNDED,
                OrderPaymentStates::STATE_AWAITING_PAYMENT,
            ])
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $orders;
    }

    /**
     * @param ?Address $address
     * @param bool $details
     * @return array
     */
    public function serializeAddress(?Address $address, $details = false): ?array
    {
        $customer = null;
        $isDefault = false;

        if ($address == null) {
            return null;
        }

        /** @var Customer $customer */
        $customer = $address->getCustomer();

        if ($customer instanceof Customer && $customer->getDefaultAddress() && $customer->getDefaultAddress()->getId() == $address->getId()) {
            $isDefault = true;
        }

        /** @var Address $address */
        $object = [
            'id' => $address->getId(),
            'ask_for' => $address->getAnnotations(),
            'full_address' => $address->getFullAddress(),
            'phone_number' => $address->getPhoneNumber(),
            'status' => $address->getStatus(),
            'type' => $address->getType(),
            'is_default' => $isDefault,
            'parent' => $this->serializeAddress($address->getParent())
        ];

        if (Address::TYPE_BILLING == $address->getType()) {
            $object['tax_id'] = $address->getTaxId();
            $serializedAddress['ask_for'] = $address->getFirstName();
        }

        if ($details) {
            $object['validated_at'] = $address->getValidatedAt();
            $object['customer'] = $this->serializeCustomer($customer);
        }

        return $object;
    }

    /**
     * @param Payment $payment
     * @return array
     */
    public function serializePayment(Payment $payment): array
    {
        return [
            'id' => $payment->getId(),
            'state' => $payment->getState(),
            'currency' => $payment->getCurrencyCode(),
            'amount' => $payment->getAmount()/100,
            'details' => $payment->getDetails(),
            'method' => [
                'id' => $payment->getMethod()->getId(),
                'code' => $payment->getMethod()->getCode(),
            ],
            'created_at' => $payment->getCreatedAt(),
            'updated_at' => $payment->getUpdatedAt(),
        ];
    }

    /**
     * @param PromotionCoupon $couponOrder
     * @return array
     */
    public function serializeCoupon(PromotionCoupon $couponOrder)
    {
        return [
            'code' => $couponOrder->getCode(),
            'type' => $couponOrder->getTypeSlug($this->channelContext->getChannel()),
        ];
    }

    /**
     * @param Adjustment $adjustment
     * @return array
     */
    public function serializeAdjustment(Adjustment $adjustment)
    {
        return [
            'type' => $adjustment->getType(),
            'label' => $adjustment->getLabel(),
            'amount' => $adjustment->getAmount() / 100,
        ];
    }

    /**
     * @param OrderItem $orderItem
     * @return array
     */
    public function serializeOrderItem(OrderItem $orderItem)
    {
        /** @var ProductVariant $variant */
        $variant = $orderItem->getVariant();

        return [
            'code' => $variant->getCode(),
            'name' => $variant->getProduct()->getName(),
            'quantity' => $orderItem->getQuantity(),
            'unit_price' => $orderItem->getUnitPrice()/100,
            'total' => $orderItem->getTotal()/100,
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    public function serializeRating(Order $order)
    {
        return [
            'rating' => $order->getRating(),
            'comment' => $order->getRatingComment(),
        ];
    }

    /**
     * @param ?Customer $customer
     * @return array
     */
    public function serializeCustomer(?Customer $customer): ?array
    {
        if (!$customer) {
            return null;
        }

        $age = $this->calculateAge($customer->getBirthday());

        $serializedCustomer = [
            'id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'first_name' => $customer->getFirstName(),
            'last_name' => $customer->getLastName(),
            'gender' => $customer->getGender(),
        ];

        if (null !== $age) {
            $serializedCustomer['age'] = $age;
        }

        return $serializedCustomer;
    }

    /**
     * @param \DateTimeInterface|null $birthday
     * @return false|int|mixed|string|null
     */
    private function calculateAge(?\DateTimeInterface $birthday)
    {
        if (!$birthday) {
            return null;
        }

        $formattedBirthday = $birthday->format('m/d/Y');
        $formattedBirthday = explode("/", $formattedBirthday);

        return (date("md", date("U", mktime(0, 0, 0, $formattedBirthday[0], $formattedBirthday[1], $formattedBirthday[2]))) > date("md")
            ? ((date("Y") - $formattedBirthday[2]) - 1)
            : (date("Y") - $formattedBirthday[2]));
    }

    /**
     * @param ShipmentInterface $shipment
     * @return array
     */
    private function serializeShipment(ShipmentInterface $shipment)
    {
        return [
            'id' => $shipment->getId(),
            'method' => [
                'code' => $shipment->getMethod()->getCode(),
                'name' => $shipment->getMethod()->getName(),
                'amount' => ($shipment->getMethod()->getConfiguration()[$this->channelContext->getChannel()->getCode()]['amount'] ?? 0)/100,
            ],
            'state' => $shipment->getState(),
            'created_at' => $shipment->getCreatedAt(),
            'updated_at' => $shipment->getUpdatedAt(),
        ];
    }
}
