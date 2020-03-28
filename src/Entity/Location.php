<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use App\Model\TimestampableTrait;
use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Table(name="app_location")
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 */
class Location implements ResourceInterface
{
    use BlameableTrait, TimestampableTrait, IpTraceableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="address", type="text", nullable=false)
     */
    private $address;

    /**
     * @var string
     * @ORM\Column(name="phone_number", type="string", length=10, nullable=false)
     */
    private $phoneNumber;

    /**
     * @var string
     * @ORM\Column(name="extension", type="string", length=4, nullable=true)
     */
    private $extension;

    /**
     * @var array
     * @ORM\Column(name="schedule", type="json", nullable=true)
     */
    private $schedule;

    /**
     * @var string
     * @ORM\Column(name="photo", type="text", nullable=true)
     */
    private $photo;

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
     * @return Location
     */
    public function setName(?string $name): Location
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return Location
     */
    public function setAddress(?string $address): Location
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     * @return Location
     */
    public function setPhoneNumber(?string $phoneNumber): Location
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     * @return Location
     */
    public function setExtension(?string $extension): Location
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return array
     */
    public function getSchedule(): ?array
    {
        return $this->schedule;
    }

    /**
     * @param array $schedule
     * @return Location
     */
    public function setSchedule(?array $schedule): Location
    {
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    /**
     * @param string $photo
     * @return Location
     */
    public function setPhoto(?string $photo): Location
    {
        $this->photo = $photo;

        return $this;
    }

    public function getFilePath(): string
    {
        return UploaderHelper::LOCATION_IMAGE.'/'.$this->getPhoto();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->name;
    }
}
