<?php

namespace App\Controller\ShopApi;

use App\Entity\Order\OrderItem;
use App\Entity\Product\ProductVariant;
use App\Model\APIResponse;
use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use App\Entity\Promotion\PromotionCoupon;
use App\Service\PaymentGatewayService;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Cart\Modifier\LimitingOrderItemQuantityModifier;
use Sylius\Component\Core\Factory\CartItemFactoryInterface;
use Sylius\Component\Order\Processor\CompositeOrderProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Core\Model\OrderInterface;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\ShopApiPlugin\Controller\Cart\AddCouponAction;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
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

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var CartItemFactoryInterface
     */
    private $orderItemFactory;

    /**
     * @var LimitingOrderItemQuantityModifier
     */
    private $itemQuantityModifier;

    /**
     * @var CompositeOrderProcessor
     */
    private $compositeOrderProcessor;

    /**
     * CartController constructor.
     * @param OrderRepository $repository
     * @param PromotionCouponRepository $couponRepository
     * @param AddCouponAction $addCouponAction
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param OrderRepository $orderRepository
     * @param CartItemFactoryInterface $cartItemFactory
     * @param LimitingOrderItemQuantityModifier $itemQuantityModifier
     * @param CompositeOrderProcessor $compositeOrderProcessor
     */
    public function __construct(OrderRepository $repository, PromotionCouponRepository $couponRepository, AddCouponAction $addCouponAction, TranslatorInterface $translator, EntityManagerInterface $entityManager, OrderRepository $orderRepository, CartItemFactoryInterface $cartItemFactory, LimitingOrderItemQuantityModifier $itemQuantityModifier, CompositeOrderProcessor $compositeOrderProcessor)
    {
        $this->repository = $repository;
        $this->couponRepository = $couponRepository;
        $this->addCouponAction = $addCouponAction;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
        $this->orderItemFactory = $cartItemFactory;
        $this->itemQuantityModifier = $itemQuantityModifier;
        $this->compositeOrderProcessor = $compositeOrderProcessor;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_get_cart",
     *     methods={"POST"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $orders = $this->getOrders();
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

        if ($mainOrder instanceof Order) {
            foreach ($variants as $variant) {
                /** @var OrderItem $orderItem */
                $orderItem = $this->orderItemFactory->createNew();
                $orderItem->setVariant($variant['variant']);
                $this->itemQuantityModifier->modify($orderItem, $variant['quantity']);

                $this->entityManager->persist($orderItem);

                $mainOrder->addItem($orderItem);
                $this->compositeOrderProcessor->process($mainOrder);
            }
        }

        $this->orderRepository->add($mainOrder);

        /** Remove previous carts */
        foreach ($orders as $index => $order) {
            if ($index > 0) {
                $this->orderRepository->remove($order);
            }
        }

        try  {
            $this->entityManager->flush();

            $statusCode = Response::HTTP_OK;
            $serialized = $this->serializeOrder($mainOrder);
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Merged...', $serialized);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);

        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.coupon_not_found'));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }
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
        $cart = $this->repository->findOneBy(['tokenValue' => $token]);
        $coupon = $this->couponRepository->findOneBy(['code' => $code]);

        if ($cart instanceof Order) {
            if ($coupon instanceof PromotionCoupon) {
                if ($coupon->isEnabled()) {
                    return $this->addCouponAction->__invoke($request);
                } else {
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.coupon_not_found'));
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
     * @return Response
     */
    public function payAction(Request $request, PaymentGatewayService $paymentService) {
        $token = $request->get('token');
        $statusCode = Response::HTTP_OK;

        /** @var Order $order */
        $order = $this->repository->findOneBy(['tokenValue' => $token]);

        $cardHolder = $request->get('card_holder');
        $cardNumber = $request->get('card_number');
        $expDate = $request->get('exp_date');
        $cvv = $request->get('cvv');

        $result = $paymentService->orderPayment($order, $cardHolder, $cardNumber, $expDate, $cvv);

        if ('00' !== $result['responseCode']) {
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $result['responseMessage'], $result);

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * Return customer's orders.
     * @return Order[]
     */
    private function getOrders()
    {
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
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $orders;
    }

    /**
     * Return serialized order object.
     * @param Order|null $order
     * @return array
     */
    private function serializeOrder(?Order $order)
    {
        return [
            'id' => $order->getId(),
            'number' => $order->getNumber(),
            'items_total' => $order->getTotalQuantity(),
            'token_value' => $order->getTokenValue(),
            'created_at' => $order->getCreatedAt()->format('c'),
        ];
    }
}
