<?php

namespace App\MessageHandler;

use App\Entity\Segment;
use App\Entity\Notification;
use App\Entity\PushNotification;
use Doctrine\ORM\EntityManagerInterface;
use App\Message\PushNotificationMessage;
use Sylius\Component\Core\OrderPaymentStates;
use App\Repository\PushNotificationRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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
            $type = $pushNotification->getPromotionType();

            if (empty($type)) {
                $type = $pushNotification->getType();
            }

            if ($type == 'banner') {
                $type = 'promotion';
            }

            $notification = new Notification($pushNotification, $user, $pushNotification->getTitle(), $pushNotification->getDescription(), $type);
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
            return $this->entityManager->getRepository('App:User\ShopUser')
                ->createQueryBuilder('u')
                ->innerJoin('u.devices', 'devices')
                ->getQuery()
                ->getResult();
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
        $fixedAmount = $segment->getFixedAmount() * 100; // Fix to match Sylius currency format.
        $purchaseTimes = $segment->getPurchaseTimes();

        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App:User\ShopUser', 'u');

        $orderCalculator = ($frequencyType == Segment::TYPE_PURCHASE_TIMES) ? 'COUNT(o.id)' : 'AVG(o.total)';
        $orderFilter = ($frequencyType == Segment::TYPE_PURCHASE_TIMES) ? $purchaseTimes : $fixedAmount;

        if (count($gender) == 0) {
            $gender[] = 'm';
            $gender[] = 'f';
            $gender[] = 'u';
        }

        if (count($gender) == 2) {
            $gender[] = 'u';
        }

        $startDate = date('Y-m-d', strtotime('-1 month'));
        $endDate = date('Y-m-d', strtotime('now'));
        $ageRangeDiscriminator = '';
        $orderFilterDiscriminator = '';

        if ($minAge && $maxAge) {
            $ageRangeDiscriminator = " AND age BETWEEN ".$minAge." AND ".$maxAge." ";
        }

        if ($frequencyType) {
            $orderFilterDiscriminator = " AND monthly_purchases >= ".$orderFilter." ";
        }

        $sql = "SELECT u.id, u.username, TIMESTAMPDIFF(YEAR, c.birthday, CURDATE()) AS age, ".$orderCalculator." as monthly_purchases
            FROM sylius_shop_user u
            LEFT JOIN sylius_customer c ON u.customer_id = c.id
            LEFT JOIN sylius_order o ON o.customer_id = c.id
            INNER JOIN app_shop_user_device d ON d.user_id = u.id
            WHERE c.gender IN ('".implode ("', '", $gender)."')
            AND o.payment_state = 'paid'
            AND o.created_at BETWEEN '".$startDate." 00:00:00' AND '".$endDate." 23:59:59'
            GROUP BY u.id
            HAVING 1 = 1
            ".$ageRangeDiscriminator."
            ".$orderFilterDiscriminator.";";

        $query = $this->entityManager->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }
}
