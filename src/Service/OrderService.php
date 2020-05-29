<?php

namespace App\Service;

use Carbon\Carbon;
use App\Entity\Holiday;
use App\Entity\AboutStore;
use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use App\Entity\Order\OrderItem;
use App\Repository\HolidayRepository;
use App\Entity\Product\ProductVariant;
use App\Repository\AboutStoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Carbon\Exceptions\InvalidFormatException;
use Doctrine\ORM\NonUniqueResultException;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Order\Processor\CompositeOrderProcessor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sylius\Component\Core\Cart\Modifier\LimitingOrderItemQuantityModifier;

class OrderService
{
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
     * OrderService constructor.
     * @param HolidayRepository $holidayRepository
     * @param AboutStoreRepository $aboutStoreRepository
     * @param OrderRepository $repository
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param LimitingOrderItemQuantityModifier $itemQuantityModifier
     * @param CompositeOrderProcessor $compositeOrderProcessor
     */
    public function __construct(HolidayRepository $holidayRepository, AboutStoreRepository $aboutStoreRepository, OrderRepository $repository, TranslatorInterface $translator, EntityManagerInterface $entityManager, LimitingOrderItemQuantityModifier $itemQuantityModifier, CompositeOrderProcessor $compositeOrderProcessor, CartItemFactoryInterface $cartItemFactory)
    {
        $this->holidayRepository = $holidayRepository;
        $this->aboutStoreRepository = $aboutStoreRepository;
        $this->repository = $repository;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->itemQuantityModifier = $itemQuantityModifier;
        $this->compositeOrderProcessor = $compositeOrderProcessor;
        $this->cartItemFactory = $cartItemFactory;
    }

    /**
     * Find next available date for order.
     * @param $preferredDeliveryDate
     * @param $scheduledDeliveryDate
     * @return Carbon
     * @throws NonUniqueResultException
     */
    public function getNextAvailableDay($preferredDeliveryDate = "", $scheduledDeliveryDate = "")
    {
        $today = Carbon::now();
        $aboutStore = $this->aboutStoreRepository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            throw new NotFoundHttpException('Settings are missing.');
        }

        try {
            $scheduledDeliveryDate = Carbon::parse($scheduledDeliveryDate);
        } catch (InvalidFormatException $exception) {
            throw new BadRequestHttpException('Settings are missing.');
        }

        if ($scheduledDeliveryDate->lessThanOrEqualTo($today)) {
            throw new BadRequestHttpException('La fecha elegida para el envío no es valida, seleccione una fecha futura.');
        }

        $daysDifference = $this->diffInDays($today, $scheduledDeliveryDate);

        if ((bool)$aboutStore->getDaysToChooseInAdvanceToPurchase() && ($daysDifference > $aboutStore->getDaysToChooseInAdvanceToPurchase())) {
            throw new BadRequestHttpException('El día de entrega seleccionado sobrepasa el rango permitido.');
        }

        $nextAvailableDay = $this->findValidDeliverDate($scheduledDeliveryDate);

        return $this->setTimeToAvailableDay($nextAvailableDay, $preferredDeliveryDate);
    }

    /**
     * Merge available carts and return merged cart.
     * @param ShopUser $user
     * @return Order
     */
    public function mergeCarts(ShopUser $user): Order
    {
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
     * @return array
     */
    public function serializeOrder(?Order $order)
    {
        return [
            'id' => $order->getId(),
            'number' => $order->getNumber(),
            'items_total' => $order->getTotalQuantity(),
            'token_value' => $order->getTokenValue(),
            'created_at' => $order->getCreatedAt()->format('c'),
            'estimated_delivery_date' => $order->getEstimatedDeliveryDate(),
            'status' => $order->getStatus(),
            'order_state' => $order->getState(),
            'checkout_state' => $order->getCheckoutState(),
            'payment_state' => $order->getPaymentState(),
            'shipping_state' => $order->getShippingState(),
            'rating' => $order->getRating(),
            'rating_comment' => $order->getRatingComment(),
        ];
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
     * @param string $start
     * @return Carbon
     */
    private function findValidDeliverDate(Carbon $scheduledDeliveryDate, $start = 'now'): Carbon
    {
        $isAvailable = false;
        $today = Carbon::parse($start);

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
     * @return Carbon
     * @throws NonUniqueResultException
     */
    private function setTimeToAvailableDay(Carbon $nextAvailableDay, $preferredDeliveryDate): Carbon
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
            ->setParameter('customer', $user->getCustomer())
            ->setParameter('state', OrderInterface::STATE_CART)
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $orders;
    }
}
