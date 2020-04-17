<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="app_segment")
 * @ORM\Entity(repositoryClass="App\Repository\SegmentRepository")
 */
class Segment implements ResourceInterface
{
    use BlameableTrait, TimestampableTrait;

    CONST TYPE_FIXED_AMOUNT = 'amount';

    CONST TYPE_PURCHASE_TIMES = 'times';

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
    private $name;

    /**
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    private $gender;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $frequencyType;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $fixedAmount;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $purchaseTimes;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\GreaterThanOrEqual(
     *     value = 18
     * )
     */
    private $minAge;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxAge;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\PushNotification",
     *     mappedBy="segment"
     * )
     */
    private $pushNotifications;

    public function __construct()
    {
        $this->pushNotifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Segment
     */
    public function setName(?string $name): Segment
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getGender(): ?array
    {
        return $this->gender;
    }

    /**
     * @param array $gender
     * @return Segment
     */
    public function setGender(?array $gender): Segment
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getFrequencyType(): ?string
    {
        return $this->frequencyType;
    }

    /**
     * @param string $frequencyType
     * @return Segment
     */
    public function setFrequencyType(?string $frequencyType): Segment
    {
        if (!in_array($frequencyType, [
            self::TYPE_FIXED_AMOUNT,
            self::TYPE_PURCHASE_TIMES,
        ])) {
            throw new BadRequestHttpException('Invalid frequency type for segment.');
        }

        $this->frequencyType = $frequencyType;

        return $this;
    }

    /**
     * @return float
     */
    public function getFixedAmount(): ?float
    {
        return $this->fixedAmount;
    }

    /**
     * @param float $fixedAmount
     * @return Segment
     */
    public function setFixedAmount(?float $fixedAmount): Segment
    {
        $this->fixedAmount = $fixedAmount;

        return $this;
    }

    /**
     * @return int
     */
    public function getPurchaseTimes(): ?int
    {
        return $this->purchaseTimes;
    }

    /**
     * @param int $purchaseTimes
     * @return Segment
     */
    public function setPurchaseTimes(?int $purchaseTimes): Segment
    {
        $this->purchaseTimes = $purchaseTimes;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    /**
     * @param int $minAge
     * @return Segment
     */
    public function setMinAge(?int $minAge): Segment
    {
        $this->minAge = $minAge;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    /**
     * @param int $maxAge
     * @return Segment
     */
    public function setMaxAge(?int $maxAge): Segment
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        if ($this->minAge && null == $this->maxAge) {
            $context->buildViolation('app.ui.segment.enter_max_age')
                ->atPath('maxAge')
                ->addViolation();
        }

        if ($this->maxAge && $this->maxAge <= $this->minAge) {
            $context->buildViolation('app.ui.segment.max_age_must_be_greater_than_min_age')
                ->atPath('maxAge')
                ->addViolation();
        }

        if ($this->frequencyType) {
            if ($this->frequencyType == self::TYPE_FIXED_AMOUNT && !$this->fixedAmount) {
                $context->buildViolation('app.ui.segment.enter_fixed_amount')
                    ->atPath('fixedAmount')
                    ->addViolation();
            }

            if ($this->frequencyType == self::TYPE_PURCHASE_TIMES && !$this->purchaseTimes) {
                $context->buildViolation('app.ui.segment.enter_purchase_times')
                    ->atPath('purchaseTimes')
                    ->addViolation();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->name;
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
            $pushNotification->setSegment($this);
        }

        return $this;
    }

    public function removePushNotification(PushNotification $pushNotification): self
    {
        if ($this->pushNotifications->contains($pushNotification)) {
            $this->pushNotifications->removeElement($pushNotification);
            // set the owning side to null (unless already changed)
            if ($pushNotification->getSegment() === $this) {
                $pushNotification->setSegment(null);
            }
        }

        return $this;
    }
}
