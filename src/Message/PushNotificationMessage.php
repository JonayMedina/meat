<?php

namespace App\Message;

use App\Entity\PushNotification;

/**
 * Class PushNotificationMessage
 * @package App\Message
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class PushNotificationMessage
{
    private $id;

    private $title;

    private $description;

    private $type;

    private $coupon;

    private $segment;

    /**
     * PushNotificationMessage constructor.
     * @param PushNotification $pushNotification
     */
    public function __construct(PushNotification $pushNotification)
    {
        $this->id = $pushNotification->getId();
        $this->title = $pushNotification->getTitle();
        $this->description = $pushNotification->getDescription();
        $this->type = $pushNotification->getType();

        $this->segment = (!$pushNotification->getSegment()) ? [] : [
            'id' => $pushNotification->getSegment()->getId(),
            'name' => $pushNotification->getSegment()->getName(),
            'gender' => $pushNotification->getSegment()->getGender(),
            'min_age' => $pushNotification->getSegment()->getMinAge(),
            'max_age' => $pushNotification->getSegment()->getMaxAge(),
            'frequency_type' => $pushNotification->getSegment()->getFrequencyType(),
            'fixed_amount' => $pushNotification->getSegment()->getFixedAmount(),
            'purchase_times' => $pushNotification->getSegment()->getPurchaseTimes(),
        ];

        $this->coupon = (!$pushNotification->getPromotionCoupon()) ? [] : [
            'id' => $pushNotification->getPromotionCoupon()->getId(),
            'code' => $pushNotification->getPromotionCoupon()->getCode(),
            'usage_limit' => $pushNotification->getPromotionCoupon()->getUsageLimit(),
            'used' => $pushNotification->getPromotionCoupon()->getUsed(),
            'enabled' => $pushNotification->getPromotionCoupon()->isEnabled(),
            'starts' => $pushNotification->getPromotionCoupon()->getPromotion()->getStartsAt(),
            'ends' => $pushNotification->getPromotionCoupon()->getPromotion()->getEndsAt(),
        ];
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getCoupon(): array
    {
        return $this->coupon;
    }

    /**
     * @return array
     */
    public function getSegment(): array
    {
        return $this->segment;
    }
}
