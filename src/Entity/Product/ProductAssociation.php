<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductAssociation as BaseProductAssociation;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_association")
 */
class ProductAssociation extends BaseProductAssociation
{
    use BlameableTrait, IpTraceableTrait;
}
