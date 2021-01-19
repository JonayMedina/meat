<?php

namespace App\EventSubscriber;

use App\Service\LogService;
use Exception;
use Ramsey\Uuid\Uuid;
use App\Entity\Order\Order;
use App\Service\AdminSyncService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class OrderSubscriber
 * @package AppBundle\EventSubscriber
 */
class OrderSubscriber implements EventSubscriber
{
    /**
     * @var AdminSyncService $adminSyncService
     */
    private $adminSyncService;

    /**
     * @var LogService
     */
    private $logService;

    /**
     * OrderSubscriber constructor.
     * @param AdminSyncService $adminSyncService
     * @param LogService $logService
     */
    public function __construct(AdminSyncService $adminSyncService, LogService $logService)
    {
        $this->adminSyncService = $adminSyncService;
        $this->logService = $logService;
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
            /** Set token value */
            $this->setTokenValue($entity, $args);

            /** Notify about new order */
            $this->checkAdminNewOrderNotification($entity, $args);

            /** Notify about rating */
            $this->checkAdminRatingNotification($entity, $args);
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
            /** Set token value */
            $this->setTokenValue($entity, $args);

            /** Notify about new order */
            $this->checkAdminNewOrderNotification($entity, $args);

            /** Notify about rating */
            $this->checkAdminRatingNotification($entity, $args);

            /** Log status changes */
            $this->logStateChange($entity, $args);
        }
    }

    /**
     * Set token value if still empty on cart.
     * @param Order $order
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    private function setTokenValue(Order $order, LifecycleEventArgs $args): void
    {
        if ($order->getTokenValue() == null) {
            $order->setTokenValue(Uuid::uuid4()->toString());

            $args->getEntityManager()->flush();
        }
    }

    /**
     * Check if we need to notify to admin about rating.
     * @param Order $order
     * @param LifecycleEventArgs $args
     */
    private function checkAdminRatingNotification(Order $order, LifecycleEventArgs $args): void
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();
        $changeSet = $unitOfWork->getEntityChangeSet($order);

        if (isset($changeSet['rating'])) {
            $this->adminSyncService->syncOrderAfterRating($order);
        }
    }

    /**
     * Check if we need to notify to admin about a new order.
     * @param Order $order
     * @param LifecycleEventArgs $args
     */
    private function checkAdminNewOrderNotification(Order $order, LifecycleEventArgs $args)
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();
        $changeSet = $unitOfWork->getEntityChangeSet($order);

        if (isset($changeSet['state']) && $changeSet['state'][0] != Order::STATE_NEW && $changeSet['state'][1] == Order::STATE_NEW) {
            // This is done by workflow event.
            // $this->adminSyncService->syncOrderAfterCheckoutCompleted($order);
        }
    }

    /**
     * @param Order $order
     * @param LifecycleEventArgs $args
     */
    private function logStateChange(Order $order, LifecycleEventArgs $args)
    {
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();
        $changeSet = $unitOfWork->getEntityChangeSet($order);

        if (isset($changeSet['adjustmentsTotal']) || isset($changeSet['total']) || isset($changeSet['itemsTotal'])) {
            $metadata = $this->logService->getMetadata(null, $order);
            $this->logService->log($metadata, true);
        }
    }
}
