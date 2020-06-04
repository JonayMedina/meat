<?php

namespace App\EventSubscriber;

use Exception;
use App\Service\AdminSyncService;
use App\Entity\Customer\Customer;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class CustomerSubscriber
 * @package AppBundle\EventSubscriber
 */
class CustomerSubscriber implements EventSubscriber
{
    /** @var AdminSyncService */
    private $adminSyncService;

    /**
     * CustomerSubscriber constructor.
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
        return ['postPersist'];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Customer) {
            /** Check if we need to send admin notification. */
            $this->checkAdminCustomerNotification($entity);
        }
    }

    /**
     * @param Customer $customer
     */
    private function checkAdminCustomerNotification(Customer $customer)
    {
        $this->adminSyncService->syncCustomerAfterCreation($customer);
    }
}
