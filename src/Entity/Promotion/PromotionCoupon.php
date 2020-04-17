<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use App\Entity\PushNotification;
use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\PromotionCoupon as BasePromotionCoupon;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_promotion_coupon")
 */
class PromotionCoupon extends BasePromotionCoupon
{
    use BlameableTrait, IpTraceableTrait;

    const TYPE_PERCENTAGE = 'order_percentage_discount';

    const TYPE_FIXED_AMOUNT = 'order_fixed_discount';

    const MAX_USAGES_PER_USER = 100000;

    /**
     * @var bool $enabled
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $enabled;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\PushNotification",
     *     mappedBy="promotionCoupon"
     * )
     */
    private $pushNotifications;

    public function __construct()
    {
        $this->pushNotifications = new ArrayCollection();
    }

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

    /**
     * Return true if coupon is out dated.
     * @return bool
     */
    public function isOutdated()
    {
        $now = time();
        $promotion = $this->getPromotion();

        if (!$promotion->getEndsAt()) {
            return false;
        }

        return ($promotion->getEndsAt()->format('U') <= $now);
    }

    /**
     * Return true if user has no quota available.
     * @return bool
     */
    public function hasNoQuota()
    {
        $promotion = $this->getPromotion();

        return ($promotion->getUsageLimit() && $promotion->getUsed() >= $promotion->getUsageLimit());
    }

    /**
     * @return Collection|PushNotification[]
     */
    public function getPushNotifications(): Collection
    {
        return $this->pushNotifications;
    }

    public function addPushNotification(PushNotification $pushNotification): self
    {
        if (!$this->pushNotifications->contains($pushNotification)) {
            $this->pushNotifications[] = $pushNotification;
            $pushNotification->setPromotionCoupon($this);
        }

        return $this;
    }

    public function removePushNotification(PushNotification $pushNotification): self
    {
        if ($this->pushNotifications->contains($pushNotification)) {
            $this->pushNotifications->removeElement($pushNotification);
            // set the owning side to null (unless already changed)
            if ($pushNotification->getPromotionCoupon() === $this) {
                $pushNotification->setPromotionCoupon(null);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->code;
    }
}
