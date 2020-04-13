<?php


namespace App\Repository;

use App\Entity\Product\Product;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;

class ProductRepository extends BaseProductRepository
{
    /**
     * @param int $taxon
     * @return array
     */
    public function findByTaxon(int $taxon): array
    {
        $qb = $this->createQueryBuilder('product');

        /**
         * @var Product[] $products
         */
        $products = $qb->select('p')
            ->from('App\Entity\Product\Product', 'p')
            ->innerJoin('App\Entity\Product\ProductTaxon', 'pt', 'p.id = pt.product')
            ->where('p.enabled like :true')
            ->andWhere('pt.taxon = :taxon')
            ->setParameter('true', 1)
            ->setParameter('taxon', $taxon)
            ->getQuery()
            ->getResult();

        return $products;
    }

    /**
     * @return array
     */
    public function findOffers(): array
    {
        $qb = $this->createQueryBuilder('product');

        /**
         * @var Product[] $products
         */
        $products = $qb->select('p')
            ->from('App\Entity\Product\Product', 'p')
            ->innerJoin('App\Entity\Product\ProductVariant', 'pv', 'pv.product = p.id')
            ->innerJoin('App\Entity\Channel\ChannelPricing', 'ch', 'ch.productVariant = pv.id')
            ->where('ch.originalPrice > ch.price')
            ->andWhere('p.enabled like :true')
            ->setParameter('true', 1)
            ->getQuery()
            ->getResult();

        return $products;
    }
}
