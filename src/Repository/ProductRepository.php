<?php


namespace App\Repository;

use App\Entity\Product\Product;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;

class ProductRepository extends BaseProductRepository
{
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
}
