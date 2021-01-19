<?php

namespace App\EventListener;

use App\Entity\Log;
use App\Entity\Order\Order;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class ApiLogListener
 * @package App\EventListener
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class ApiLogListener
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
     * ApiLogListener constructor.
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
     * @param ResponseEvent $response
     */
    public function onKernelResponse(ResponseEvent $response)
    {
        $request = $response->getRequest();
        $firewallContext = $request->attributes->get('_firewall_context');

        if ($firewallContext == 'security.firewall.map.context.sylius_shop_api') {
            $metadata = $this->getMetadata($response);
            $this->log($metadata);
        }
    }

    /**
     * Collect request metadata.
     * @param ResponseEvent $response
     * @return array
     */
    private function getMetadata(ResponseEvent $response): array
    {
        $request = $response->getRequest();
        $metadata = [];

        $metadata['method'] = $request->getMethod();
        $metadata['uri'] = $request->getPathInfo();
        $metadata['content'] = $request->getContent();
        $metadata['content_type'] = $request->getContentType();
        $metadata['query'] = $request->getQueryString();
        $metadata['response'] = $response->getResponse()->getContent();
        $metadata['status_code'] = $response->getResponse()->getStatusCode();
        $metadata['order'] = null;

        $tokenValue = $request->get('token', $request->get('tokenValue', null));

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

                $metadata['order'] = json_encode($serializedOrder);
            }
        }

        return $metadata;
    }

    /**
     * Create log.
     * @param array $metadata
     */
    private function log(array $metadata)
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

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
