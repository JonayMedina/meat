<?php

namespace App\Command;

use App\Entity\Promotion\Promotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PromotionRepository;

class DisableExpiredCouponsCommand extends Command
{
    protected static $defaultName = 'app:disable-expired-coupons';

    /**
     * @var PromotionRepository $promotionRepository
     */
    private $promotionRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * DisableExpiredCouponsCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param PromotionRepository $promotionRepository
     */
    public function __construct(EntityManagerInterface $entityManager, PromotionRepository $promotionRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->promotionRepository = $promotionRepository;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this->setDescription('Automatically disable expired coupons.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = date('Y-m-d H:i:s');
        $counter = 0;

        /**
         * Expired coupons.
         * @var Promotion[] $promotions
         */
        $promotions = $this->promotionRepository
            ->createQueryBuilder('promotion')
            ->innerJoin('promotion.coupons', 'coupons')
            ->andWhere('promotion.endsAt <= :today')
            ->andWhere('promotion.couponBased = :couponBased')
            ->andWhere('coupons.enabled = :enabled')
            ->setParameter('today', $now)
            ->setParameter('couponBased', true)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();

        $counter += $this->disablePromotions($promotions, 'outdated');

        $promotions = $this->promotionRepository
            ->createQueryBuilder('promotion')
            ->innerJoin('promotion.coupons', 'coupons')
            ->andWhere('promotion.couponBased = :couponBased')
            ->andWhere('coupons.enabled = :enabled')
            ->andWhere('promotion.used >= promotion.usageLimit')
            ->setParameter('couponBased', true)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();

        $counter += $this->disablePromotions($promotions, 'no-quota');

        $io->success('Coupons expired: ' . $counter);

        return 0;
    }

    /**
     * Disable given promotions array.
     * @param array $promotions
     * @param string $reason
     * @return int
     */
    private function disablePromotions(array $promotions, $reason = ''): int
    {
        /** @var Promotion[] $promotions */
        $counter = 0;

        foreach ($promotions as $promotion) {
            $coupons = $promotion->getCoupons();

            foreach ($coupons as $coupon) {
                $coupon->setEnabled(false);
                $coupon->setDisabledReason($reason);
                $counter++;
            }
        }

        $this->entityManager->flush();

        return $counter;
    }
}
