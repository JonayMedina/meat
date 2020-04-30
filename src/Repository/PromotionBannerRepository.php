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

    // /**
    //  * @return PromotionBanner[] Returns an array of PromotionBanner objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PromotionBanner
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
