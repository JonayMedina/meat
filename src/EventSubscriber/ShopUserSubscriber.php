<?php

namespace App\EventSubscriber;

use App\Entity\User\ShopUser;
use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CompanySubscriber
 * @package AppBundle\EventSubscriber
 */
class ShopUserSubscriber implements EventSubscriber
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
        return ['postPersist'];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ShopUser) {
            $entity->setTermsAndConditionsAcceptedAt(new DateTime());
            $entity->setEnabled(true);

            $this->container->get('doctrine')->getManager()->flush();
        }
    }
}
