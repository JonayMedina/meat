<?php

namespace App\EventListener;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
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
    protected $entityManager;

    /**
     * ApiLogListener constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
