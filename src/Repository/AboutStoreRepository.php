<?php

namespace App\Repository;

use App\Entity\AboutStore;
use Psr\Cache\InvalidArgumentException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method AboutStore|null find($id, $lockMode = null, $lockVersion = null)
 * @method AboutStore|null findOneBy(array $criteria, array $orderBy = null)
 * @method AboutStore[]    findAll()
 * @method AboutStore[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AboutStoreRepository extends ServiceEntityRepository
{
    /**
     * @var AdapterInterface
     */
    private $cache;

    public function __construct(ManagerRegistry $registry, AdapterInterface $cache)
    {
        parent::__construct($registry, AboutStore::class);
        $this->cache = $cache;
    }

    /**
     * @param bool $cacheable
     * @return AboutStore|null Returns TermsAndConditions object
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     */
    public function findLatest($cacheable = false)
    {
        if (!$cacheable) {
            return $this->createQueryBuilder('about_store')
                ->orderBy('about_store.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        $item = $this->cache->getItem('_about_store_');

        if (!$item->isHit()) {
            $result = $this->createQueryBuilder('about_store')
                ->orderBy('about_store.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $item->set($result);
            $item->expiresAfter(15); // 15 secs of cache
            $this->cache->save($item);
        }

        $result = $item->get();

        return $result;
    }
}
