<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use App\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Table(name="app_holiday")
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="App\Repository\HolidayRepository")
 */
class Holiday implements ResourceInterface
{
    use TimestampableTrait, BlameableTrait, IpTraceableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=200, nullable=false)
     * @Serializer\Expose()
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="month_name", type="string", length=150, nullable=true)
     * @Serializer\Expose()
     */
    private $monthName;

    /**
     * @var \DateTime()
     * @ORM\Column(name="date", type="date", nullable=false)
     * @Serializer\Expose()
     */
    private $date;

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
     * @return Holiday
     */
    public function setName(?string $name): Holiday
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return Holiday
     */
    public function setDate(\DateTime $date): Holiday
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getMonthName(): ?string
    {
        return $this->monthName;
    }

    /**
     * @param string $monthName
     * @return Holiday
     */
    public function setMonthName(?string $monthName): Holiday
    {
        $this->monthName = $monthName;

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
