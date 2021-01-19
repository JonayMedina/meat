<?php

namespace App\Service;

use App\Entity\Log;
use App\Entity\Order\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;

/**
 * Class LogService
 * @package App\Service
 */
class LogService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderService $orderService
     */
    private $orderService;

    /**
     * LogService constructor.
     * @param EntityManagerInterface $entityManager
     * @param OrderRepository $orderRepository
     * @param OrderService $orderService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        OrderService $orderService
    )
    {
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }

    /**
     * Collect request metadata.
     * @param ResponseEvent|null $response
     * @param Order|null $order
     * @return array
     */
    public function getMetadata(ResponseEvent $response = null, Order $order = null): array
    {
        $metadata = [];

        if ($response instanceof ResponseEvent) {
            $request = $response->getRequest();

            $metadata['response'] = $response->getResponse()->getContent();
            $metadata['status_code'] = $response->getResponse()->getStatusCode();
            $metadata['order'] = null;

            $tokenValue = $request->get('token', $request->get('tokenValue', null));
        } else {
            $request = Request::createFromGlobals();
            $metadata['order'] = null;

            $tokenValue = $order->getTokenValue();
        }

        $metadata['method'] = $request->getMethod();
        $metadata['uri'] = $request->getPathInfo();
        $metadata['content'] = $request->getContent();
        $metadata['content_type'] = $request->getContentType();
        $metadata['query'] = $request->getQueryString();

        if (!empty($tokenValue)) {
            /** @var Order $order */
            $order = $this->orderRepository->findOneBy(['tokenValue' => $tokenValue]);

            if ($order instanceof Order) {
                $serializedOrder = $this->orderService->serializeOrder($order, true);
                unset($serializedOrder['total_quantity']);
                unset($serializedOrder['created_at']);
                unset($serializedOrder['estimated_delivery_date']);
                unset($serializedOrder['status']);
                unset($serializedOrder['order_state']);
                unset($serializedOrder['checkout_state']);
                unset($serializedOrder['payment_state']);
                unset($serializedOrder['shipping_state']);
                unset($serializedOrder['customer']);
                unset($serializedOrder['rating']);
                unset($serializedOrder['id']);
                unset($serializedOrder['number']);
                unset($serializedOrder['token_value']);
                unset($serializedOrder['shipping_address']);
                unset($serializedOrder['billing_address']);

                $metadata['order'] = json_encode($serializedOrder);
            }
        }

        return $metadata;
    }

    /**
     * Create log.
     * @param array $metadata
     * @param bool $isStateChangeRelated
     */
    public function log(array $metadata, $isStateChangeRelated = false)
    {
        $log = new Log();
        $log->setMethod($metadata['method']);
        $log->setUri($metadata['uri']);
        $log->setContent($metadata['content']);
        $log->setContentType($metadata['content_type']);
        $log->setQuery($metadata['query']);
        $log->setResponse($metadata['response']);
        $log->setStatusCode($metadata['status_code']);
        $log->setOrder($metadata['order']);
        $log->setIsStateChangeLog($isStateChangeRelated);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
