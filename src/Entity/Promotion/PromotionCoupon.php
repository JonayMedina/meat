<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PromotionCoupon as BasePromotionCoupon;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_promotion_coupon")
 */
class PromotionCoupon extends BasePromotionCoupon
{
    const TYPE_PERCENTAGE = 'order_percentage_discount';

    const TYPE_FIXED_AMOUNT = 'order_fixed_discount';

    const MAX_USAGES_PER_USER = 100000;

    /**
     * @var bool $enabled
     * @ORM\Column(type="integer", nullable=true)
     */
    private $enabled;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return PromotionCoupon
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Virtual field, return coupon type.
     * @param $channel
     * @return string|null
     */
    public function getType(string $channel): ?string
    {
        $promotion = $this->getPromotion();
        $configuration = $promotion->getActions()[0]->getConfiguration()[$channel] ?? $promotion->getActions()[0]->getConfiguration();

        if (isset($configuration['amount'])) {
            return 'Cantidad fija - Q' . ($configuration['amount']/100);
        }

        if (isset($configuration['percentage'])) {
            return 'Porcentaje de descuento - ' . ($configuration['percentage'] * 100) . '%';
        }

        return null;
    }

    /**
     * Virtual field, return coupon type as slug.
     * @param $channel
     * @return string|null
     */
    public function getTypeSlug(string $channel): ?string
    {
        $promotion = $this->getPromotion();
        $configuration = $promotion->getActions()[0]->getConfiguration()[$channel] ?? $promotion->getActions()[0]->getConfiguration();

        if (isset($configuration['amount'])) {
            return self::TYPE_FIXED_AMOUNT;
        }

        if (isset($configuration['percentage'])) {
            return self::TYPE_PERCENTAGE;
        }

        return null;
    }

    public function getValue(string $channel)
    {
        $promotion = $this->getPromotion();
        $configuration = $promotion->getActions()[0]->getConfiguration()[$channel] ?? $promotion->getActions()[0]->getConfiguration();

        if (isset($configuration['amount'])) {
            return $configuration['amount']/100;
        }

        if (isset($configuration['percentage'])) {
            return $configuration['percentage'] * 100;
        }

        return null;
    }
}
