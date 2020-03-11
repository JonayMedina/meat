<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct
{
    use BlameableTrait, IpTraceableTrait;

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}
