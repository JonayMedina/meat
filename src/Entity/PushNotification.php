<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use App\Entity\Promotion\PromotionCoupon;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ORM\Table(name="app_push_notifications")
 * @ORM\Entity(repositoryClass="App\Repository\PushNotificationRepository")
 */
class PushNotification implements ResourceInterface
{
    use BlameableTrait, TimestampableTrait, IpTraceableTrait;

    const TYPE_PROMOTION = 'promotion';

    const TYPE_INFO = 'info';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $type = self::TYPE_PROMOTION;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $sent = false;

    /**
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    private $response;

    /**
     * @var PromotionCoupon $promotionCoupon
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Promotion\PromotionCoupon",
     *     inversedBy="pushNotifications"
     * )
     * @ORM\JoinColumn(
     *     name="coupon_id",
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    private $promotionCoupon;

    /**
     * @var Segment $segment
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Segment",
     *     inversedBy="pushNotifications"
     * )
     * @ORM\JoinColumn(
     *     name="segment_id",
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    private $segment;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return PushNotification
     */
    public function setTitle(?string $title): PushNotification
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return PushNotification
     */
    public function setDescription(?string $description): PushNotification
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PushNotification
     */
    public function setType(?string $type): PushNotification
    {
        if (!in_array($type, [
            self::TYPE_PROMOTION,
            self::TYPE_INFO
        ])) {
            throw new BadRequestHttpException('Invalid type for Push Notification.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return Segment
     */
    public function getSegment(): ?Segment
    {
        return $this->segment;
    }

    /**
     * @param Segment $segment
     * @return PushNotification
     */
    public function setSegment(?Segment $segment)
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * @return PromotionCoupon
     */
    public function getPromotionCoupon(): ?PromotionCoupon
    {
        return $this->promotionCoupon;
    }

    /**
     * @param PromotionCoupon $promotionCoupon
     * @return PushNotification
     */
    public function setPromotionCoupon(?PromotionCoupon $promotionCoupon): ?PushNotification
    {
        $this->promotionCoupon = $promotionCoupon;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->title;
    }
}
