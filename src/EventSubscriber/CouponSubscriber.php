<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use App\Entity\Promotion\PromotionCoupon;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CompanySubscriber
 * @package AppBundle\EventSubscriber
 */
class CouponSubscriber implements EventSubscriber
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
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof PromotionCoupon) {
            $entity->setCreatedBy($this->security->getUser() ? $this->security->getUser()->getUsername() : null);

            $this->container->get('doctrine')->getManager()->flush();
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof PromotionCoupon) {
            $entity->setUpdatedBy($this->security->getUser() ? $this->security->getUser()->getUsername() : null);

            $this->container->get('doctrine')->getManager()->flush();
        }
    }

}
