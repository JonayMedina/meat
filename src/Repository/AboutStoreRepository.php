<?php

namespace App\Repository;

use App\Entity\AboutStore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AboutStore|null find($id, $lockMode = null, $lockVersion = null)
 * @method AboutStore|null findOneBy(array $criteria, array $orderBy = null)
 * @method AboutStore[]    findAll()
 * @method AboutStore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AboutStoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AboutStore::class);
    }

    /**
     * @return AboutStore|null Returns TermsAndConditions object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findLatest()
    {
        return $this->createQueryBuilder('about_store')
            ->orderBy('about_store.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
