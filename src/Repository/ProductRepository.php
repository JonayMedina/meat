<?php


namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use App\Entity\Product\Product;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;

class ProductRepository extends BaseProductRepository
{
    /**
     * Perform a search query.
     * @param string $phrase
     * @param string $locale
     * @param int|null $limit
     * @param int|null $offset
     * @return QueryBuilder
     */
    public function searchQuery(string $phrase, string $locale, ?int $limit = 100, ?int $offset = 0): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere('translation.name LIKE :name')
            ->setParameter('name', '%' . $phrase . '%')
            ->setParameter('locale', $locale)
            ->setMaxResults($limit)
            ->setFirstResult($offset);
    }

    /**
     * @param int $taxon
     * @param int|null $limit
     * @return array
     */
    public function findByTaxon(int $taxon, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('p');

        $query = $qb->select('p')
            ->innerJoin('App\Entity\Product\ProductTaxon', 'pt', 'WITH', 'p.id = pt.product')
            ->where('p.enabled like :true')
            ->andWhere('pt.taxon = :taxon')
            ->setParameter('true', true)
            ->setParameter('taxon', $taxon);

        if (isset($limit)) {
            $query->setMaxResults($limit);
        }

        /**
         * @var Product[] $products
         */
        $products = $query
            ->getQuery()
            ->getResult();

        return $products;
    }

    /**
     * @return array
     */
    public function findOffers(): array
    {
        $qb = $this->createQueryBuilder('p');

        /**
         * @var Product[] $products
         */
        $products = $qb->select('p')
            ->innerJoin('App\Entity\Product\ProductVariant', 'pv', 'WITH','pv.product = p.id')
            ->innerJoin('App\Entity\Channel\ChannelPricing', 'ch', 'WITH', 'ch.productVariant = pv.id')
            ->where('ch.originalPrice > ch.price')
            ->andWhere('p.enabled like :true')
            ->setParameter('true', true)
            ->getQuery()
            ->getResult();

        return $products;
    }
}
