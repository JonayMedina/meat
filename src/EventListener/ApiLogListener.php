<?php

namespace App\EventListener;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

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
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $firewallContext = $request->attributes->get('_firewall_context');

        if ($firewallContext == 'security.firewall.map.context.sylius_shop_api') {
            $metadata = $this->getMetadata($request);
            $this->log($metadata);
        }
    }

    /**
     * Collect request metadata.
     * @param Request $request
     * @return array
     */
    private function getMetadata(Request $request): array
    {
        $metadata = [];

        $metadata['method'] = $request->getMethod();
        $metadata['uri'] = $request->getPathInfo();
        $metadata['content'] = $request->getContent();
        $metadata['content_type'] = $request->getContentType();
        $metadata['query'] = $request->getQueryString();

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

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
