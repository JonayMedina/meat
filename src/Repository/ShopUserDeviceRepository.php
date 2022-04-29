<?php

namespace App\Repository;

use App\Entity\ShopUserDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopUserDevice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopUserDevice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopUserDevice[]    findAll()
 * @method ShopUserDevice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopUserDeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopUserDevice::class);
    }

    // /**
    //  * @return ShopUserDevice[] Returns an array of ShopUserDevice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ShopUserDevice
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
