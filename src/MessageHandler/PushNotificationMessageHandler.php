<?php

namespace App\MessageHandler;

use App\Entity\Promotion\PromotionCoupon;
use App\Entity\Segment;
use App\Service\FCMService;
use App\Entity\PushNotification;
use App\Message\PushNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PushNotificationRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class PushNotificationMessageHandler
 * @package App\MessageHandler
 */
class PushNotificationMessageHandler implements MessageHandlerInterface
{
    const CHUNK_SIZE = 1000;

    /**
     * @var FCMService
     */
    private $fcmService;

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
     * @param FCMService $FCMService
     * @param EntityManagerInterface $entityManager
     * @param PushNotificationRepository $repository
     * @param ContainerInterface $container
     */
    public function __construct(FCMService $FCMService, EntityManagerInterface $entityManager, PushNotificationRepository $repository, ContainerInterface $container)
    {
        $this->fcmService = $FCMService;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->container = $container;
    }

    public function __invoke(PushNotificationMessage $message)
    {
        $response = [];
        /** @var PushNotification $pushNotification */
        $pushNotification = $this->repository->find($message->getPushId());
        $chunks = array_chunk($this->getUsers($pushNotification), self::CHUNK_SIZE);

        /** @var PromotionCoupon $coupon */
        $coupon = $pushNotification->getPromotionCoupon();

        foreach ($chunks as $chunk) {
            foreach ($chunk as $user) {
                $response[] = $user . ': ' . $pushNotification->getTitle();
            }
        }

        /**
         * Mark as sent
         */
        $pushNotification->setSent(true);
        $pushNotification->setResponse($response);

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
            // TODO: Return all users.
        }

        $users = $this->getUsersBySegment($segment);

        return ['Tokio', 'Profesor', 'Helsinki', 'Rio', 'Estocolmo', 'Nairobi', 'Berlín', 'Denver', 'Marsella', 'Moscú', 'Oslo'];
    }

    /**
     * Return users by segment.
     * @param Segment $segment
     */
    private function getUsersBySegment(Segment $segment)
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

        $users = $queryBuilder
            ->getQuery()
            ->getResults();
    }
}
