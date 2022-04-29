<?php

namespace App\Repository;

use App\Entity\TermsAndConditions;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method TermsAndConditions|null find($id, $lockMode = null, $lockVersion = null)
 * @method TermsAndConditions|null findOneBy(array $criteria, array $orderBy = null)
 * @method TermsAndConditions[]    findAll()
 * @method TermsAndConditions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TermsAndConditionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TermsAndConditions::class);
    }

    /**
     * @return TermsAndConditions|null Returns TermsAndConditions object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findLatest()
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
