<?php

namespace App\MessageHandler;

use App\Entity\Segment;
use App\Entity\Notification;
use Doctrine\ORM\QueryBuilder;
use App\Entity\PushNotification;
use App\Message\PushNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PushNotificationRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class PushNotificationMessageHandler
 * @package App\MessageHandler
 */
class PushNotificationMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PushNotificationRepository
     */
    private $repository;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * PushNotificationMessageHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param PushNotificationRepository $repository
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $entityManager, PushNotificationRepository $repository, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->container = $container;
    }

    public function __invoke(PushNotificationMessage $message)
    {
        /** @var PushNotification $pushNotification */
        $pushNotification = $this->repository->find($message->getPushId());
        $users = $this->getUsers($pushNotification);

        foreach ($users as $user) {
            $notification = new Notification($pushNotification, $user, $pushNotification->getTitle(), $pushNotification->getDescription(), $pushNotification->getType());
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        /**
         * Mark as sent
         */
        $pushNotification->setSent(true);

        $this->entityManager->flush();
    }

    /**
     * @param PushNotification $pushNotification
     * @return array
     */
    private function getUsers(PushNotification $pushNotification)
    {
        $segment = $pushNotification->getSegment();

        if (!$segment instanceof Segment) {
            return $this->entityManager->getRepository('App:User\ShopUser')->findAll();
        }

        return $this->getUsersBySegment($segment);
    }

    /**
     * Return users by segment.
     * @param Segment $segment
     */
    private function getUsersBySegment(Segment $segment): array
    {
        $minAge = $segment->getMinAge();
        $maxAge = $segment->getMaxAge();
        $gender = $segment->getGender();
        $frequencyType = $segment->getFrequencyType();
        $fixedAmount = $segment->getFixedAmount();
        $purchaseTimes = $segment->getPurchaseTimes();

        // TODO: Make a working query...

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->container->get('sylius.repository.shop_user')
            ->createQueryBuilder('shop_user');

        /**
         * Age filter...
         */
        if ($minAge && $maxAge) {
            // TODO: Create age filter
        }

        /**
         * Gender filter...
         */
        if ($gender) {
            if (count($gender) == 1) {
                $queryBuilder
                    ->andWhere('shop_user.gender = :gender')
                    ->setParameter('gender', $gender[0]);
            }
        }

        /**
         * Monthly purchase times filter...
         */
        if ($frequencyType == Segment::TYPE_PURCHASE_TIMES) {
            // TODO: Create monthly purchases filter...
        }

        /**
         * Monthly fixed amount filter...
         */
        if ($frequencyType == Segment::TYPE_FIXED_AMOUNT) {
            // TODO: Create monthly fixed amount filter...
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
