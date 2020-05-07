<?php

namespace App\Repository;

use App\Entity\PromotionBanner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PromotionBanner|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromotionBanner|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromotionBanner[]    findAll()
 * @method PromotionBanner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromotionBannerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromotionBanner::class);
    }

    /**
     * @return int|mixed|string
     */
    public function findAvailable()
    {
        $now = date('Y-m-d H:i:s');

        return $this->createQueryBuilder('promotion_banner')
            ->andWhere('promotion_banner.startDate <= :now')
            ->andWhere('promotion_banner.endDate >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }
}
