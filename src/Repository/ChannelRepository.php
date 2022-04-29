<?php

namespace App\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sylius\Bundle\ChannelBundle\Doctrine\ORM\ChannelRepository as BaseChannelRepository;

/**
 * Class ChannelRepository
 * @package App\Repository
 */
class ChannelRepository extends BaseChannelRepository
{
    /**
     * @return int|mixed|string
     * @throws NonUniqueResultException
     */
    public function findLatest() {
        $queryBuilder = $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(1);

        $channel = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!$channel) {
            throw new NotFoundHttpException('Channel not found.');
        }

        return $channel;
    }
}
