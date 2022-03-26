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
            ->innerJoin('o.translations', 'translations')
            ->leftJoin('o.images', 'images')
            ->leftJoin('o.options', 'options')
            ->andWhere('translations.name LIKE :name')
            ->andWhere('translations.locale = :locale')
            ->andWhere('o.enabled = :enabled')
            ->addSelect(['translations', 'images', 'options'])
            ->setParameter('name', '%' . $phrase . '%')
            ->setParameter('locale', $locale)
            ->setParameter('enabled', true)
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
            ->innerJoin('p.productTaxons', 'pt')
            ->leftJoin('p.translations', 'translations')
            ->leftJoin('p.images', 'images')
            ->leftJoin('p.options', 'options')
            ->addSelect(['pt', 'translations', 'images', 'options'])
            ->andWhere('p.enabled like :true')
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
            ->innerJoin('p.variants', 'pv')
            ->innerJoin('pv.channelPricings', 'ch')
            ->leftJoin('p.translations', 'translations')
            ->leftJoin('p.images', 'images')
            ->leftJoin('p.options', 'options')
            ->addSelect(['pv', 'ch', 'translations', 'images', 'options'])
            ->andWhere('ch.originalPrice > ch.price')
            ->andWhere('p.enabled like :true')
            ->setParameter('true', true)
            ->getQuery()
            ->getResult();

        return $products;
    }
}
