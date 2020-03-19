<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use App\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Table(name="app_about_store")
 * @ORM\Entity(repositoryClass="App\Repository\AboutStoreRepository")
 */
class AboutStore implements ResourceInterface
{
    use TimestampableTrait, BlameableTrait, IpTraceableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="about_us", type="text", nullable=true)
     */
    private $aboutUs;

    /**
     * @var string
     * @ORM\Column(name="first_purchase_message", type="text", nullable=true)
     */
    private $firstPurchaseMessage;

    /**
     * @var string
     * @ORM\Column(name="new_address_message", type="text", nullable=true)
     */
    private $newAddressMessage;

    /**
     * @var string
     * @ORM\Column(name="phrase", type="string", length=125, nullable=true)
     */
    private $phrase;

    /**
     * @var string
     * @ORM\Column(name="author", type="string", length=30, nullable=true)
     */
    private $author;

    /**
     * @var float
     * @ORM\Column(name="maximum_purchase_value", type="decimal", scale=2, precision=13, nullable=true)
     */
    private $maximumPurchaseValue;

    /**
     * @var float
     * @ORM\Column(name="minimum_purchase_value", type="decimal", scale=2, precision=13, nullable=true)
     */
    private $minimumPurchaseValue;

    /**
     * @var int
     * @ORM\Column(name="days_to_choose_in_advance_to_purchase", type="integer", nullable=true)
     */
    private $daysToChooseInAdvanceToPurchase;

    /**
     * @var bool
     * @ORM\Column(name="show_product_search_box", type="boolean")
     */
    private $showProductSearchBox = true;

    /**
     * @var array
     * @ORM\Column(name="delivery_hours", type="json", nullable=true)
     */
    private $deliveryHours = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAboutUs(): ?string
    {
        return $this->aboutUs;
    }

    /**
     * @param string $aboutUs
     * @return AboutStore
     */
    public function setAboutUs(?string $aboutUs): self
    {
        $this->aboutUs = $aboutUs;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhrase(): ?string
    {
        return $this->phrase;
    }

    /**
     * @param string $phrase
     * @return AboutStore
     */
    public function setPhrase(?string $phrase): self
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return AboutStore
     */
    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstPurchaseMessage(): ?string
    {
        return $this->firstPurchaseMessage;
    }

    /**
     * @param string $firstPurchaseMessage
     * @return AboutStore
     */
    public function setFirstPurchaseMessage(?string $firstPurchaseMessage): self
    {
        $this->firstPurchaseMessage = $firstPurchaseMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewAddressMessage(): ?string
    {
        return $this->newAddressMessage;
    }

    /**
     * @param string $newAddressMessage
     * @return AboutStore
     */
    public function setNewAddressMessage(?string $newAddressMessage): self
    {
        $this->newAddressMessage = $newAddressMessage;

        return $this;
    }

    /**
     * @return float
     */
    public function getMaximumPurchaseValue(): ?float
    {
        return $this->maximumPurchaseValue;
    }

    /**
     * @param float $maximumPurchaseValue
     * @return AboutStore
     */
    public function setMaximumPurchaseValue(?float $maximumPurchaseValue): self
    {
        $this->maximumPurchaseValue = $maximumPurchaseValue;

        return $this;
    }

    /**
     * @return float
     */
    public function getMinimumPurchaseValue(): ?float
    {
        return $this->minimumPurchaseValue;
    }

    /**
     * @param float $minimumPurchaseValue
     * @return AboutStore
     */
    public function setMinimumPurchaseValue(?float $minimumPurchaseValue): self
    {
        $this->minimumPurchaseValue = $minimumPurchaseValue;

        return $this;
    }

    /**
     * @return int
     */
    public function getDaysToChooseInAdvanceToPurchase(): ?int
    {
        return $this->daysToChooseInAdvanceToPurchase;
    }

    /**
     * @param int $daysToChooseInAdvanceToPurchase
     * @return AboutStore
     */
    public function setDaysToChooseInAdvanceToPurchase(?int $daysToChooseInAdvanceToPurchase): self
    {
        $this->daysToChooseInAdvanceToPurchase = $daysToChooseInAdvanceToPurchase;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowProductSearchBox(): bool
    {
        return $this->showProductSearchBox;
    }

    /**
     * @param bool $showProductSearchBox
     * @return AboutStore
     */
    public function setShowProductSearchBox(bool $showProductSearchBox): self
    {
        $this->showProductSearchBox = $showProductSearchBox;

        return $this;
    }

    /**
     * @param bool $enabled
     * @return array
     */
    public function getDeliveryHours($enabled = true): ?array
    {
        if ($enabled) {
            $hours = [];

            foreach ($this->deliveryHours as $schedule) {
                if (isset($schedule['enabled']) && $schedule['enabled']) {
                    $hours[] = $schedule;
                }
            }

            return $hours;
        }

        return $this->deliveryHours;
    }

    /**
     * @param array $deliveryHours
     * @return AboutStore
     */
    public function setDeliveryHours(?array $deliveryHours): AboutStore
    {
        $this->deliveryHours = $deliveryHours;

        return $this;
    }
}
