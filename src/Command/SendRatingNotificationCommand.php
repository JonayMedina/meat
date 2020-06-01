<?php

namespace App\Command;

use App\Entity\Order\Order;
use App\Entity\Notification;
use App\Entity\User\ShopUser;
use App\Entity\PushNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;

class SendRatingNotificationCommand extends Command
{
    protected static $defaultName = 'app:send-rating-notification';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * SendRatingNotificationCommand constructor.
     * @param OrderRepository $orderRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(OrderRepository $orderRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->entityManager = $entityManager;
    }


    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setDescription('Add a short description for your command');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->getOrders() as $order) {
            /** @var ShopUser $user */
            $user = $order->getUser();

            if ($user instanceof ShopUser) {
                $notification = new Notification(null, $user, '', '', PushNotification::TYPE_RATE_ORDER);
                $this->entityManager->persist($notification);
            }

            $order->setRatingNotificationSent(true);

            $this->entityManager->flush();
        }

        $io->success('Everything ok...');

        return 0;
    }

    /**
     * Return orders ready to notify
     * @return Order[]
     */
    private function getOrders()
    {
        return $this->orderRepository
            ->createQueryBuilder('o')
            ->andWhere('o.ratingNotificationSent = :ratingNotificationSent')
            ->andWhere('o.estimatedDeliveryDate >= :estimatedDeliveryDate')
            ->setParameter('estimatedDeliveryDate', date('Y-m-d H:i:s'))
            ->setParameter('ratingNotificationSent', false)
            ->getQuery()
            ->getResult();
    }
}
