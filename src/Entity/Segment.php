<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
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
     * @var string
     * @ORM\Column(type="string", length=3, nullable=true)
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
     */
    private $minAge;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxAge;

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
     * @return string
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     * @return Segment
     */
    public function setGender(?string $gender): Segment
    {
        if (!in_array($gender, [
            CustomerInterface::FEMALE_GENDER,
            CustomerInterface::MALE_GENDER,
            CustomerInterface::UNKNOWN_GENDER
        ])) {
            throw new BadRequestHttpException('Invalid gender type for segment.');
        }

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
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->name;
    }
}
