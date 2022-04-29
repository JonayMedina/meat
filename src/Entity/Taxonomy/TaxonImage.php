<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\TaxonImage as BaseTaxonImage;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_taxon_image")
 */
class TaxonImage extends BaseTaxonImage
{
    use BlameableTrait, IpTraceableTrait;
}
