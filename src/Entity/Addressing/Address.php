<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use App\Model\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_address")
 */
class Address extends BaseAddress
{
    use BlameableTrait;
}
