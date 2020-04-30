<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use App\Entity\Product\ProductVariant;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Table(name="app_promotion_banner")
 * @ORM\Entity(repositoryClass="App\Repository\PromotionBannerRepository")
 */
class PromotionBanner implements ResourceInterface
{
    use BlameableTrait, TimestampableTrait, IpTraceableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Product\ProductVariant",
     *     inversedBy="promotionBanners"
     * )
     * @ORM\JoinColumn(
     *     name="product_variant_id",
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    private $productVariant;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private $name;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $photoWeb;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $photoTablet;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $photoMobile;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $photoApp;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getProductVariant(): ?ProductVariant
    {
        return $this->productVariant;
    }

    /**
     * @param ProductVariant $productVariant
     * @return PromotionBanner
     */
    public function setProductVariant(?ProductVariant $productVariant)
    {
        $this->productVariant = $productVariant;

        return $this;
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
     * @return PromotionBanner
     */
    public function setName(?string $name): PromotionBanner
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return PromotionBanner
     */
    public function setStartDate(?\DateTime $startDate): PromotionBanner
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     * @return PromotionBanner
     */
    public function setEndDate(?\DateTime $endDate): PromotionBanner
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoWeb(): ?string
    {
        return $this->photoWeb;
    }

    /**
     * @param string $photoWeb
     * @return PromotionBanner
     */
    public function setPhotoWeb(?string $photoWeb): PromotionBanner
    {
        $this->photoWeb = $photoWeb;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoTablet(): ?string
    {
        return $this->photoTablet;
    }

    /**
     * @param string $photoTablet
     * @return PromotionBanner
     */
    public function setPhotoTablet(?string $photoTablet): PromotionBanner
    {
        $this->photoTablet = $photoTablet;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoMobile(): ?string
    {
        return $this->photoMobile;
    }

    /**
     * @param string $photoMobile
     * @return PromotionBanner
     */
    public function setPhotoMobile(?string $photoMobile): PromotionBanner
    {
        $this->photoMobile = $photoMobile;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoApp(): ?string
    {
        return $this->photoApp;
    }

    /**
     * @param string $photoApp
     * @return PromotionBanner
     */
    public function setPhotoApp(?string $photoApp): PromotionBanner
    {
        $this->photoApp = $photoApp;

        return $this;
    }
}
