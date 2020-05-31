<?php

namespace App\EventSubscriber;

use App\Service\FCMService;
use App\Entity\Notification;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class NotificationSubscriber
 * @package AppBundle\EventSubscriber
 */
class NotificationSubscriber implements EventSubscriber
{
    /**
     * @var FCMService
     */
    private $fcmService;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * NotificationSubscriber constructor.
     * @param FCMService $FCMService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(FCMService $FCMService, EntityManagerInterface $entityManager)
    {
        $this->fcmService = $FCMService;
        $this->entityManager = $entityManager;
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
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Notification) {
            /** Send to fcm here... */
            $user = $entity->getUser();
            $devices = $user->getDevices();
            $deviceIds = [];

            foreach ($devices as $device) {
                $deviceIds[] = $device->getKey();
            }

            $response = $this->fcmService->send([
                'title' => $entity->getTitle(),
                'body' => $entity->getText()
            ], [
                'type' => $entity->getType(),
                'notification_id' => $entity->getId(),
            ], $deviceIds);

            $entity->setResponse($response);
            $this->entityManager->flush();
        }
    }
}
