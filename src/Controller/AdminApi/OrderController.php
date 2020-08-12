<?php

namespace App\Controller\AdminApi;

use SM\SMException;
use SM\Factory\Factory;
use App\Model\APIResponse;
use App\Entity\Order\Order;
use App\Service\OrderService;
use App\Service\PaymentGatewayService;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Order\OrderTransitions;
use Symfony\Component\HttpFoundation\Request;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Shipping\ShipmentTransitions;
use Sylius\Component\Core\OrderShippingTransitions;
use Sylius\Component\Mailer\Sender\SenderInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * OrderController
 * @Route("/orders")
 */
class OrderController extends AbstractFOSRestController
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var Factory
     */
    private $stateMachineFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /** @var SenderInterface */
    private $sender;

    /**
     * DashboardController constructor.
     * @param OrderRepository $orderRepository
     * @param OrderService $orderService
     * @param Factory $stateMachineFactory
     * @param EntityManagerInterface $entityManager
     * @param SenderInterface $sender
     */
    public function __construct(
        OrderRepository $orderRepository,
        OrderService $orderService,
        Factory $stateMachineFactory,
        EntityManagerInterface $entityManager,
        SenderInterface $sender
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->entityManager = $entityManager;
        $this->sender = $sender;
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="admin_api_orders_show",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $order = $this->getOrder($request);

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeOrder($order, true)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}/send.{_format}",
     *     name="admin_api_orders_send",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     * @throws SMException
     */
    public function sendAction(Request $request)
    {
        $order = $this->getOrder($request);

        /** Cart: new -> fulfilled */
        $stateMachine = $this->stateMachineFactory->get($order, OrderTransitions::GRAPH);

        if ($stateMachine->can(OrderTransitions::TRANSITION_FULFILL)) {
            $stateMachine->apply(OrderTransitions::TRANSITION_FULFILL);
        }

        /** OrderShippingState: ready -> shipped */
        $stateMachine = $this->stateMachineFactory->get($order, OrderShippingTransitions::GRAPH);

        if ($stateMachine->can(OrderShippingTransitions::TRANSITION_SHIP)) {
            $stateMachine->apply(OrderShippingTransitions::TRANSITION_SHIP);
        }

        foreach ($order->getShipments() as $shipment) {
            /** Shipment: ready -> shipped */
            $stateMachine = $this->stateMachineFactory->get($shipment, ShipmentTransitions::GRAPH);

            if ($stateMachine->can(ShipmentTransitions::TRANSITION_SHIP)) {
                $stateMachine->apply(ShipmentTransitions::TRANSITION_SHIP);
            }
        }

        $this->sender->send('order_shipped', [$order->getCustomer()->getEmail()], ['order' => $order]);
        $this->entityManager->flush();

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeOrder($order, true)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}/paid.{_format}",
     *     name="admin_api_orders_paid",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     * @throws SMException
     */
    public function paidAction(Request $request)
    {
        $order = $this->getOrder($request);

        /** OrderPaymentState: awaiting_payment -> paid */
        $stateMachine = $this->stateMachineFactory->get($order, OrderPaymentTransitions::GRAPH);

        if ($stateMachine->can(OrderPaymentTransitions::TRANSITION_PAY)) {
            $stateMachine->apply(OrderPaymentTransitions::TRANSITION_PAY);
        }

        foreach ($order->getPayments() as $payment) {
            /** Payment: ready -> paid */
            $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

            if ($stateMachine->can(PaymentTransitions::TRANSITION_COMPLETE)) {
                $stateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
            }
        }

        $this->entityManager->flush();

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeOrder($order, true)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}/cancel.{_format}",
     *     name="admin_api_orders_cancel",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     * @throws SMException
     */
    public function cancelAction(Request $request)
    {
        $order = $this->getOrder($request);

        if ($order->getState() == OrderInterface::STATE_FULFILLED) {
            $order->setState(OrderInterface::STATE_NEW);
            $this->entityManager->flush();
        }

        /** Order state: new -> cancelled */
        $stateMachine = $this->stateMachineFactory->get($order, OrderTransitions::GRAPH);

        if ($stateMachine->can(OrderTransitions::TRANSITION_CANCEL)) {
            $stateMachine->apply(OrderTransitions::TRANSITION_CANCEL);
        }

        /** Cancel all shipments */
        foreach ($order->getShipments() as $shipment) {
            $shipment->setState(ShipmentInterface::STATE_READY);
            $this->entityManager->flush();

            /** Shipment: ready -> cancelled */
            $stateMachine = $this->stateMachineFactory->get($shipment, ShipmentTransitions::GRAPH);

            if ($stateMachine->can(ShipmentTransitions::TRANSITION_CANCEL)) {
                $stateMachine->apply(ShipmentTransitions::TRANSITION_CANCEL);
            }
        }

        /** Revert all payments */
        foreach ($order->getPayments() as $payment) {
            /** Payment: completed -> refund */
            $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

            if ($order->getLastPayment()->getMethod()->getCode() == PaymentGatewayService::PAYMENT_METHOD_EPAY) {
                if ($stateMachine->can(PaymentTransitions::TRANSITION_REFUND)) {
                    $stateMachine->apply(PaymentTransitions::TRANSITION_REFUND);
                }
            }

            if ($order->getLastPayment()->getMethod()->getCode() == PaymentGatewayService::PAYMENT_METHOD_CASH_ON_DELIVERY) {

                // Set to: awaiting_payment
                $payment->setState(PaymentInterface::STATE_NEW);
                $this->entityManager->flush();

                if ($stateMachine->can(PaymentTransitions::TRANSITION_CANCEL)) {
                    $stateMachine->apply(PaymentTransitions::TRANSITION_CANCEL);
                }
            }
        }

        if ($order->getPaymentState() == OrderPaymentStates::STATE_AWAITING_PAYMENT) {
            /** Cart: awaiting_payment -> cancelled */
            $stateMachine = $this->stateMachineFactory->get($order, OrderPaymentTransitions::GRAPH);

            if ($stateMachine->can(OrderPaymentTransitions::TRANSITION_CANCEL)) {
                $stateMachine->apply(OrderPaymentTransitions::TRANSITION_CANCEL);
            }
        }

        if ($order->getPaymentState() == OrderPaymentStates::STATE_PAID && $order->getLastPayment()->getMethod()->getCode() == PaymentGatewayService::PAYMENT_METHOD_CASH_ON_DELIVERY) {
            // Set to: awaiting_payment
            $order->setPaymentState(OrderPaymentStates::STATE_AWAITING_PAYMENT);
            $this->entityManager->flush();

            /** Cart: awaiting_payment -> cancelled */
            $stateMachine = $this->stateMachineFactory->get($order, OrderPaymentTransitions::GRAPH);

            if ($stateMachine->can(OrderPaymentTransitions::TRANSITION_CANCEL)) {
                $stateMachine->apply(OrderPaymentTransitions::TRANSITION_CANCEL);
            }
        }

        if ($order->getPaymentState() == OrderPaymentStates::STATE_PAID && $order->getLastPayment()->getMethod()->getCode() == PaymentGatewayService::PAYMENT_METHOD_EPAY) {
            // TODO: Make refund here...


            /** Cart: paid -> refunded */
            $stateMachine = $this->stateMachineFactory->get($order, OrderPaymentTransitions::GRAPH);

            if ($stateMachine->can(OrderPaymentTransitions::TRANSITION_REFUND)) {
                $stateMachine->apply(OrderPaymentTransitions::TRANSITION_REFUND);
            }
        }

        // Change shipment status
        /** Cart: new -> cancelled */
        $order->setShippingState(OrderShippingStates::STATE_READY);
        $this->entityManager->flush();

        $stateMachine = $this->stateMachineFactory->get($order, OrderShippingTransitions::GRAPH);

        if ($stateMachine->can(OrderShippingTransitions::TRANSITION_CANCEL)) {
            $stateMachine->apply(OrderShippingTransitions::TRANSITION_CANCEL);
        }

        $this->sender->send('order_cancelled', [$order->getCustomer()->getEmail()], ['order' => $order]);
        $this->entityManager->flush();

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeOrder($order, true)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Order
     */
    private function getOrder(Request $request): Order
    {
        $id = $request->get('id');
        /** @var Order $order */
        $order = $this->orderRepository->find($id);

        if (!$order instanceof Order) {
            throw new NotFoundHttpException('Order not found.');
        }

        return $order;
    }
}
