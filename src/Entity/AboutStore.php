<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use App\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ORM\Table(name="app_about_store")
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="App\Repository\AboutStoreRepository")
 */
class AboutStore implements ResourceInterface
{
    const THEME_DARK = 'dark';
    const THEME_LIGHT = 'light';

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
     * @ORM\Column(name="about_us", type="text", nullable=true)
     * @Serializer\Expose()
     */
    private $aboutUs;

    /**
     * @var string
     * @ORM\Column(name="first_purchase_message", type="text", nullable=true)
     * @Serializer\Expose()
     */
    private $firstPurchaseMessage;

    /**
     * @var string
     * @ORM\Column(name="new_address_message", type="text", nullable=true)
     * @Serializer\Expose()
     */
    private $newAddressMessage;

    /**
     * @var string
     * @ORM\Column(name="phrase", type="string", length=125, nullable=true)
     * @Serializer\Expose()
     */
    private $phrase;

    /**
     * @var string
     * @ORM\Column(name="author", type="string", length=30, nullable=true)
     * @Serializer\Expose()
     */
    private $author;

    /**
     * @var float
     * @ORM\Column(name="maximum_purchase_value", type="decimal", scale=2, precision=13, nullable=true)
     * @Serializer\Expose()
     */
    private $maximumPurchaseValue;

    /**
     * @var float
     * @ORM\Column(name="minimum_purchase_value", type="decimal", scale=2, precision=13, nullable=true)
     * @Serializer\Expose()
     */
    private $minimumPurchaseValue;

    /**
     * @var int
     * @ORM\Column(name="days_to_choose_in_advance_to_purchase", type="integer", nullable=true)
     * @Serializer\Expose()
     */
    private $daysToChooseInAdvanceToPurchase;

    /**
     * @var bool
     * @ORM\Column(name="show_product_search_box", type="boolean")
     * @Serializer\Expose()
     */
    private $showProductSearchBox = true;

    /**
     * @var array
     * @ORM\Column(name="delivery_hours", type="json", nullable=true)
     * @Serializer\Expose()
     */
    private $deliveryHours = [];

    /**
     * @var string
     * @ORM\Column(name="facebook_url", type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $facebookUrl;

    /**
     * @var string
     * @ORM\Column(name="twitter_url", type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $twitterUrl;

    /**
     * @var string
     * @ORM\Column(name="instagram_url", type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $instagramUrl;

    /**
     * @var string
     * @ORM\Column(name="pinterest_url", type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $pinterestUrl;

    /**
     * @var string
     * @ORM\Column(name="app_store_url", type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $appStoreUrl;

    /**
     * @var string
     * @ORM\Column(name="play_store_url", type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $playStoreUrl;

    /**
     * @var string
     * @ORM\Column(name="theme", type="string", length=100, nullable=true)
     * @Serializer\Expose()
     */
    private $theme = self::THEME_DARK;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Expose()
     */
    private $productsUpdatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Expose()
     */
    private $categoriesUpdatedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Expose()
     */
    private $couponsUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $complaintsEmail;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private $contactEmail;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Serializer\Expose()
     */
    private $phoneNumber;

    /**
     * Settings API Usage.
     * @var string
     * @Serializer\Expose()
     */
    public $currencyCode;

    /**
     * Settings API Usage.
     * @var string
     * @Serializer\Expose()
     */
    public $currencySymbol;

    /**
     * Settings API Usage.
     * @var string
     * @Serializer\Expose()
     */
    public $termsAndConditionsUrl;

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

    /**
     * @return string
     */
    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    /**
     * @param string $facebookUrl
     * @return AboutStore
     */
    public function setFacebookUrl(?string $facebookUrl): AboutStore
    {
        $this->facebookUrl = $facebookUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTwitterUrl(): ?string
    {
        return $this->twitterUrl;
    }

    /**
     * @param string $twitterUrl
     * @return AboutStore
     */
    public function setTwitterUrl(?string $twitterUrl): AboutStore
    {
        $this->twitterUrl = $twitterUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    /**
     * @param string $instagramUrl
     * @return AboutStore
     */
    public function setInstagramUrl(?string $instagramUrl): AboutStore
    {
        $this->instagramUrl = $instagramUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPinterestUrl(): ?string
    {
        return $this->pinterestUrl;
    }

    /**
     * @param string $pinterestUrl
     * @return AboutStore
     */
    public function setPinterestUrl(?string $pinterestUrl): AboutStore
    {
        $this->pinterestUrl = $pinterestUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppStoreUrl(): ?string
    {
        return $this->appStoreUrl;
    }

    /**
     * @param string $appStoreUrl
     * @return AboutStore
     */
    public function setAppStoreUrl(?string $appStoreUrl): AboutStore
    {
        $this->appStoreUrl = $appStoreUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlayStoreUrl(): ?string
    {
        return $this->playStoreUrl;
    }

    /**
     * @param string $playStoreUrl
     * @return AboutStore
     */
    public function setPlayStoreUrl(?string $playStoreUrl): AboutStore
    {
        $this->playStoreUrl = $playStoreUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTheme(): ?string
    {
        if (!$this->theme) {
            return self::THEME_DARK;
        }

        return $this->theme;
    }

    /**
     * @param string $theme
     * @return AboutStore
     */
    public function setTheme(?string $theme): AboutStore
    {
        if (!in_array($theme, [self::THEME_DARK, self::THEME_LIGHT])) {
            throw new BadRequestHttpException('Invalid theme selection.');
        }

        $this->theme = $theme;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getProductsUpdatedAt(): ?\DateTime
    {
        return $this->productsUpdatedAt;
    }

    /**
     * @param \DateTime $productsUpdatedAt
     * @return AboutStore
     */
    public function setProductsUpdatedAt(?\DateTime $productsUpdatedAt): AboutStore
    {
        $this->productsUpdatedAt = $productsUpdatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCategoriesUpdatedAt(): ?\DateTime
    {
        return $this->categoriesUpdatedAt;
    }

    /**
     * @param \DateTime $categoriesUpdatedAt
     * @return AboutStore
     */
    public function setCategoriesUpdatedAt(?\DateTime $categoriesUpdatedAt): AboutStore
    {
        $this->categoriesUpdatedAt = $categoriesUpdatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCouponsUpdatedAt(): ?\DateTime
    {
        return $this->couponsUpdatedAt;
    }

    /**
     * @param \DateTime $couponsUpdatedAt
     * @return AboutStore
     */
    public function setCouponsUpdatedAt(?\DateTime $couponsUpdatedAt): AboutStore
    {
        $this->couponsUpdatedAt = $couponsUpdatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getComplaintsEmail(): ?string
    {
        return $this->complaintsEmail;
    }

    /**
     * @param string $complaintsEmail
     * @return AboutStore
     */
    public function setComplaintsEmail(?string $complaintsEmail): AboutStore
    {
        $this->complaintsEmail = $complaintsEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    /**
     * @param string $contactEmail
     * @return AboutStore
     */
    public function setContactEmail(?string $contactEmail): AboutStore
    {
        $this->contactEmail = $contactEmail;

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
     * @return AboutStore
     */
    public function setPhoneNumber(?string $phoneNumber): AboutStore
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return 'MH Settings';
    }
}
