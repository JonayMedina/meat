<?php

namespace App\EventListener;

use App\Entity\User\AdminUser;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LoginListener
 * @package App\EventListener
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class LoginListener
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * LoginListener constructor.
     * @param RouterInterface $router
     * @param EventDispatcherInterface $dispatcher
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(RouterInterface $router, EventDispatcherInterface $dispatcher, TokenStorageInterface $tokenStorage)
    {
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        /**
         * Get the User entity
         * @var AdminUser|UserInterface $user
         */
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof AdminUser && null == $user->getLastPasswordUpdate()) {
            $this->dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'onKernelResponse'));
        }
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        /**
         * Get the User entity
         * @var AdminUser|UserInterface $user
         */
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof AdminUser && null == $user->getLastPasswordUpdate()) {
            /** Force to update password redirect */
            $event->setResponse(new RedirectResponse($this->router->generate("app_update_password")));
        }
    }
}
