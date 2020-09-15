<?php

namespace App\Repository;

use App\Entity\PushNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PushNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method PushNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method PushNotification[]    findAll()
 * @method PushNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PushNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PushNotification::class);
    }

    // /**
    //  * @return PushNotification[] Returns an array of PushNotification objects
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
    public function findOneBySomeField($value): ?PushNotification
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
