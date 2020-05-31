<?php

namespace App\EventSubscriber;

use Exception;
use App\Entity\Addressing\Address;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class AddressSubscriber
 * @package AppBundle\EventSubscriber
 */
class AddressSubscriber implements EventSubscriber
{
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

        if ($entity instanceof Address) {
            /** Auto validate billing address */
            if (Address::TYPE_BILLING === $entity->getType()) {
                $entity->setStatus(Address::STATUS_VALIDATED);
                $args->getEntityManager()->flush();
            }
        }
    }
}
