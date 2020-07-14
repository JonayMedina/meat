<?php

namespace App\EventSubscriber;

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
     * OrderSubscriber constructor.
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
            $this->adminSyncService->syncOrderAfterCheckoutCompleted($order);
        }
    }
}
