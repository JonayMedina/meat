<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Promotion\Model\PromotionAction as BasePromotionAction;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_promotion_action")
 * @Gedmo\Loggable
 */
class PromotionAction extends BasePromotionAction
{
    use BlameableTrait, IpTraceableTrait;

    /**
     * @var array
     * @Gedmo\Versioned
     */
    protected $configuration = [];
}
