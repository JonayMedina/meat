<?php

namespace App\EventListener;

use App\Service\LogService;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Class ApiLogListener
 * @package App\EventListener
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class ApiLogListener
{
    /**
     * @var LogService
     */
    private $logService;

    /**
     * ApiLogListener constructor.
     * @param LogService $logService
     */
    public function __construct(LogService $logService) {
        $this->logService = $logService;
    }

    /**
     * @param ResponseEvent $response
     */
    public function onKernelResponse(ResponseEvent $response)
    {
        $request = $response->getRequest();
        $firewallContext = $request->attributes->get('_firewall_context');

        if ($firewallContext == 'security.firewall.map.context.sylius_shop_api') {
            $metadata = $this->logService->getMetadata($response);
            $this->logService->log($metadata);
        }
    }
}
