<?php

namespace App\EventSubscriber;

use App\Entity\Order\Order;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CompanySubscriber
 * @package AppBundle\EventSubscriber
 */
class OrderSubscriber implements EventSubscriber
{
    /** @var ContainerInterface $container */
    private $container;

    /**
     * @var Security $security
     */
    private $security;

    public function __construct(ContainerInterface $serviceContainer, Security $security)
    {
        $this->container = $serviceContainer;
        $this->security = $security;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postPersist', 'postUpdate'];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Order) {
            if ($entity->getTokenValue() == null) {
                $entity->setTokenValue(Uuid::uuid4()->toString());

                $this->container->get('doctrine')->getManager()->flush();
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Order) {
            if ($entity->getTokenValue() == null) {
                $entity->setTokenValue(Uuid::uuid4()->toString());

                $this->container->get('doctrine')->getManager()->flush();
            }
        }
    }
}
