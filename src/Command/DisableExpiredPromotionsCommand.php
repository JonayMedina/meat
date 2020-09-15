<?php

namespace App\Command;

use App\Entity\PromotionBanner;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PromotionBannerRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableExpiredPromotionsCommand extends Command
{
    protected static $defaultName = 'app:disable-expired-promotions';

    /**
     * @var PromotionBannerRepository $promotionRepository
     */
    private $promotionRepository;

    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * DisableExpiredCouponsCommand constructor.
     * @param EntityManagerInterface $entityManager
     * @param PromotionBannerRepository $promotionRepository
     */
    public function __construct(EntityManagerInterface $entityManager, PromotionBannerRepository $promotionRepository)
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
         * Expired banners.
         * @var PromotionBanner[] $banners
         */
        $banners = $this->promotionRepository
            ->createQueryBuilder('promotion_banner')
            ->andWhere('promotion_banner.endDate <= :today')
            ->andWhere('promotion_banner.enabled = :enabled')
            ->setParameter('today', $now)
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();

        $counter += $this->disableBanners($banners);

        $io->success('Banners expired: ' . $counter);

        return 0;
    }

    /**
     * Disable given promotions array.
     * @param array $banners
     * @return int
     */
    private function disableBanners(array $banners): int
    {
        /** @var PromotionBanner[] $banners */
        $counter = 0;

        foreach ($banners as $banner) {
            $banner->setEnabled(false);
            $counter++;
        }

        $this->entityManager->flush();

        return $counter;
    }
}
