<?php

namespace App\EventSubscriber;

use Exception;
use App\Message\Sync;
use App\Service\AdminSyncService;
use App\Entity\Addressing\Address;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class AddressSubscriber
 * @package AppBundle\EventSubscriber
 */
class AddressSubscriber implements EventSubscriber
{
    /** @var AdminSyncService */
    private $adminSyncService;

    /**
     * AddressSubscriber constructor.
     * @param AdminSyncService $adminSyncService
     */
    public function __construct(AdminSyncService $adminSyncService)
    {
        $this->adminSyncService = $adminSyncService;
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

        if ($entity instanceof Address) {
            /** Auto validate billing address */
            if (Address::TYPE_BILLING === $entity->getType()) {
                $entity->setStatus(Address::STATUS_VALIDATED);
                $args->getEntityManager()->flush();
            } else {
                /** Send to validation process */
                $this->adminSyncService->syncAddressAfterCreation($entity);
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

        if ($entity instanceof Address) {
            /** Auto validate billing address */
            if (Address::TYPE_BILLING === $entity->getType()) {
                $entity->setStatus(Address::STATUS_VALIDATED);
                $args->getEntityManager()->flush();
            }

            /** Send to validation process */
            $this->adminSyncService->syncAddressAfterCreation($entity, Sync::TYPE_UPDATE);
        }
    }
}
