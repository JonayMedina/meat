<?php

namespace App\Controller\ShopApi;

use Carbon\Carbon;
use SM\Factory\Factory;
use App\Model\APIResponse;
use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use App\Service\OrderService;
use App\Entity\Customer\Customer;
use App\Entity\Shipping\Shipment;
use App\Entity\Addressing\Address;
use App\Service\PaymentGatewayService;
use App\Entity\Shipping\ShippingMethod;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AboutStoreRepository;
use App\Entity\Promotion\PromotionCoupon;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Symfony\Contracts\Translation\TranslatorInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\ShopApiPlugin\Controller\Cart\AddCouponAction;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\AddressRepository;
use Sylius\ShopApiPlugin\Controller\Cart\RemoveCouponAction;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sylius\Bundle\PromotionBundle\Doctrine\ORM\PromotionCouponRepository;

/**
 * CartController
 * @Route("/carts")
 */
class CartController extends AbstractFOSRestController
{
    /**
     * @var OrderRepository
     */
    private $repository;

    /** @var PromotionCouponRepository */
    private $couponRepository;

    /** @var AddCouponAction */
    private $addCouponAction;

    /** @var RemoveCouponAction */
    private $removeCouponAction;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @var AboutStoreRepository
     */
    private $aboutStoreRepository;

    /**
     * @var Factory
     */
    private $stateMachineFactory;

    /** @var OrderProcessorInterface */
    private $orderProcessor;

    /**
     * CartController constructor.
     * @param OrderRepository $repository
     * @param PromotionCouponRepository $couponRepository
     * @param AddCouponAction $addCouponAction
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param OrderService $orderService
     * @param AddressRepository $addressRepository
     * @param AboutStoreRepository $aboutStoreRepository
     * @param Factory $stateMachineFactory
     * @param RemoveCouponAction $removeCouponAction
     * @param OrderProcessorInterface $orderProcessor
     */
    public function __construct(
        OrderRepository $repository,
        PromotionCouponRepository $couponRepository,
        AddCouponAction $addCouponAction,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        OrderService $orderService,
        AddressRepository $addressRepository,
        AboutStoreRepository $aboutStoreRepository,
        Factory $stateMachineFactory,
        RemoveCouponAction $removeCouponAction,
        OrderProcessorInterface $orderProcessor
    ) {
        $this->repository = $repository;
        $this->couponRepository = $couponRepository;
        $this->addCouponAction = $addCouponAction;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->orderService = $orderService;
        $this->addressRepository = $addressRepository;
        $this->aboutStoreRepository = $aboutStoreRepository;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->removeCouponAction = $removeCouponAction;
        $this->orderProcessor = $orderProcessor;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_get_cart",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        /** @var ShopUser $user */
        $user = $this->getUser();

        try  {
            $mainOrder = $this->orderService->mergeCarts($user);

            $statusCode = Response::HTTP_OK;
            $serialized = $this->orderService->serializeOrder($mainOrder);
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Merged...', $serialized);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);

        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $exception->getMessage());
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @Route(
     *     "/{token}/address.{_format}",
     *     name="shop_api_cart_add_address",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function addAddressAction(Request $request, $token)
    {
        $statusCode = Response::HTTP_OK;

        $addressId = $request->get('address_id');
        $type = $request->get('type');

        /** @var Order $cart */
        $cart = $this->repository->findOneBy(['tokenValue' => $token]);
        $this->recalculate($cart);

        /** @var Address $address */
        $address = $this->addressRepository->find($addressId);

        if (!in_array($type, [Address::TYPE_BILLING, Address::TYPE_SHIPPING])) {
            throw new BadRequestHttpException('Bad address type');
        }

        if (!$cart instanceof Order) {
            throw new NotFoundHttpException('Cart not found.');
        }

        /** @var Customer $customer */
        $customer = $cart->getCustomer();

        if (!$address instanceof Address || !$address->getCustomer() instanceof Customer || !$customer instanceof Customer || $address->getCustomer()->getId() != $customer->getId()) {
            throw new NotFoundHttpException('Address not found.');
        }

        $addressCloned = clone $address;
        $addressCloned->setCustomer(null);
        $addressCloned->setParent($address);

        if (Address::TYPE_BILLING == $type) {
            $cart->setBillingAddress($addressCloned);
        }

        if (Address::TYPE_SHIPPING == $type) {
            $cart->setShippingAddress($addressCloned);
        }

        $this->entityManager->persist($addressCloned);

        /** OrderCheckoutState: cart -> addressed */
        $stateMachine = $this->stateMachineFactory->get($cart, OrderCheckoutTransitions::GRAPH);
        if ($stateMachine->can(OrderCheckoutTransitions::TRANSITION_ADDRESS)) {
            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_ADDRESS);
        }

        /** OrderCheckoutState: addressed -> shipping_skipped */
        $stateMachine = $this->stateMachineFactory->get($cart, OrderCheckoutTransitions::GRAPH);
        if ($stateMachine->can(OrderCheckoutTransitions::TRANSITION_SKIP_SHIPPING)) {
            $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SKIP_SHIPPING);
        }

        $this->entityManager->flush();

        $response = $this->orderService->serializeOrder($cart);

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{token}/coupon.{_format}",
     *     name="shop_api_cart_add_coupon",
     *     methods={"GET"}
     * )
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function addCouponAction(Request $request, $token) {
        $code = $request->get('coupon');

        /** @var Order $cart */
        $cart = $this->repository->findOneBy(['tokenValue' => $token]);
        $this->recalculate($cart);

        $coupon = $this->couponRepository->findOneBy(['code' => $code]);

        if ($cart instanceof Order) {
            if ($coupon instanceof PromotionCoupon) {
                if ($coupon->isEnabled()) {
                    if ($coupon->getPromotion()->getEndsAt() && $coupon->getPromotion()->getEndsAt()->format('U') < time()) {
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        setlocale(LC_ALL,"es_ES");
                        $date = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B de %Y", $coupon->getPromotion()->getEndsAt()->format('U')));
                        $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.coupon_expired_at', ['%date%' => $date]));
                    } else {
                        $this->isInIncompleteCart($coupon);
                        $response = $this->addCouponAction->__invoke($request);
                        $this->recalculate($cart);

                        return $response;
                    }
                } else {
                    if ($coupon->getPromotion()->getEndsAt() && $coupon->getPromotion()->getEndsAt()->format('U') < time()) {
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        setlocale(LC_ALL,"es_ES");
                        $date = iconv('ISO-8859-2', 'UTF-8', strftime("%A, %d de %B de %Y", $coupon->getPromotion()->getEndsAt()->format('U')));
                        $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.coupon_expired_at', ['%date%' => $date]));
                    } else {
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.coupon_not_found'));
                    }
                }
            } else {
                $statusCode = Response::HTTP_NOT_FOUND;
                $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.coupon_not_found'));
            }
        } else {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.not_found'));
        }

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{token}/complete.{_format}",
     *     name="shop_api_pay_cart",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @param PaymentGatewayService $paymentService
     * @param SenderInterface $sender
     * @return Response
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function payAction(Request $request, PaymentGatewayService $paymentService, SenderInterface $sender) {
        $token = $request->get('token');
        $statusCode = Response::HTTP_OK;

        try {
            /** @var Order $order */
            $order = $this->repository->findOneBy(['tokenValue' => $token]);
            $this->recalculate($order);

            if (!$order instanceof Order) {
                throw new NotFoundHttpException('Cart not found');
            }

            $aboutStore = $this->aboutStoreRepository->findLatest();

            if (($order->getTotal()/100) > $aboutStore->getMaximumPurchaseValue()) {
                throw new BadRequestHttpException('La orden excede el máximo permitido.');
            }

            if (($order->getTotal()/100) < $aboutStore->getMinimumPurchaseValue()) {
                throw new BadRequestHttpException('La orden no cumple con el mínimo de compra permitido.');
            }

            $type = $request->get('type');

            if ('credit_card' == $type) {
                try {
                    $cardHolder = trim($request->get('card_holder'));
                    $cardNumber = trim($request->get('card_number'));
                    $expDate = trim($request->get('exp_date'));
                    $cvv = trim($request->get('cvv'));

                    if (strlen($expDate) === 3) {
                        $year = substr($expDate , 0, 2);
                        $month = '0'.substr($expDate , 2, 1);
                        $expDate = $year . $month;
                    }

                    $type = APIResponse::TYPE_INFO;
                    $result = $paymentService->orderPayment($order, $cardHolder, $cardNumber, $expDate, $cvv);
                    $message = $result['responseMessage'];

                    if ('00' !== $result['responseCode']) {
                        $statusCode = Response::HTTP_BAD_REQUEST;
                        $type = APIResponse::TYPE_ERROR;

                        if (empty($message)) {
                            $message = 'Parece que hubo un error, inténtalo más tarde.';
                        }
                    } else {
                        /**
                         * Seems everything was Ok, response code == 00
                         * Inject order into response
                         */
                        $result['order'] = $this->orderService->serializeOrder($order);
                        $sender->send('order_ticket', [$order->getCustomer()->getEmail()], ['order' => $order]);
                    }

                    $response = new APIResponse($statusCode, $type, $message, $result);

                    $view = $this->view($response, $statusCode);

                    return $this->handleView($view);
                } catch (\Exception $exception) {
                    $response = new APIResponse(Response::HTTP_BAD_REQUEST, APIResponse::TYPE_ERROR, 'Error on payment gateway', []);
                    $view = $this->view($response, $statusCode);

                    return $this->handleView($view);
                }
            }

            if ('cash_on_delivery' == $type) {
                $result = $paymentService->cashOnDelivery($order);

                /** Inject order into response */
                $result['order'] = $this->orderService->serializeOrder($order);
                $sender->send('order_ticket', [$order->getCustomer()->getEmail()], ['order' => $order]);

                $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, $result['message'] ?? '', $result);
                $view = $this->view($response, $statusCode);

                return $this->handleView($view);
            }

            throw new BadRequestHttpException('Invalid payment type');
        } catch (\Exception $exception) {
            $order->setState('cart');

            $statusCode = Response::HTTP_BAD_REQUEST;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $exception->getMessage(), []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @Route(
     *     "/{token}/schedule-my-delivery.{_format}",
     *     name="shop_api_schedule_my_delivery",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function scheduleMyDeliveryAction(Request $request) {
        $token = $request->get('token');

        $statusCode = Response::HTTP_OK;
        $preferredDeliveryDate = $request->get('preferred_delivery_date');
        $scheduledDeliveryDate = $request->get('scheduled_delivery_date');

        $nextAvailableDay = $this->orderService->getNextAvailableDay($preferredDeliveryDate, $scheduledDeliveryDate);

        /** @var Order $order */
        $order = $this->repository->findOneBy(['tokenValue' => $token]);
        $this->recalculate($order);

        $order->setEstimatedDeliveryDate($nextAvailableDay);
        $order->setScheduledDeliveryDate(Carbon::parse($scheduledDeliveryDate));
        $order->setPreferredDeliveryTime($preferredDeliveryDate);
        $this->entityManager->flush();

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Ok', [
            'token' => $order->getTokenValue(),
            'estimated_delivery_date' => $order->getEstimatedDeliveryDate(),
            'order' => $this->orderService->serializeOrder($order)
        ]);

        $this->recalculate($order);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{token}/re-calculate.{_format}",
     *     name="shop_api_re_calculate_cart",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return Response
     */
    public function recalculateAction(Request $request)
    {
        $token = $request->get('token');
        $statusCode = Response::HTTP_OK;

        /** @var Order $order */
        $order = $this->repository->findOneBy(['tokenValue' => $token]);

        if (!$order instanceof Order) {
            throw new NotFoundHttpException('Cart not found');
        }

        $this->recalculate($order);

        $this->entityManager->flush();
        $serialized = $this->orderService->serializeOrder($order);

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Success', $serialized);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @param PromotionCoupon $coupon
     */
    private function isInIncompleteCart(PromotionCoupon $coupon) {
        $cart = $this->repository->findOneBy(['promotionCoupon' => $coupon, 'state' => Order::STATE_CART]);

        if ($cart instanceof Order) {
            $cart->setPromotionCoupon(null);
            $this->orderProcessor->process($cart);
        }
    }

    /**
     * @param Order $order
     */
    private function addShipping(Order $order)
    {
        $shippingMethod = $this->entityManager->getRepository('App:Shipping\ShippingMethod')
            ->findOneBy(['code' => ShippingMethod::DEFAULT_SHIPPING_METHOD]);

        if ($shippingMethod && !$order->hasShipments()) {
            $shipment = new Shipment();
            $shipment->setOrder($order);
            $shipment->setMethod($shippingMethod);
            $shipment->setCreatedAt(new \DateTime());
            $shipment->setState('ready');

            $this->entityManager->persist($shipment);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Order $order
     */
    private function recalculate(Order $order)
    {
        /**
         * Add shipment here...
         * @var ShippingMethod $shippingMethod
         */
        $this->addShipping($order);

        $order->recalculateAdjustmentsTotal();
    }
}
