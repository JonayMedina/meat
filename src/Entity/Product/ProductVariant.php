<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\PromotionBanner;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_variant")
 */
class ProductVariant extends BaseProductVariant
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\PromotionBanner",
     *     mappedBy="productVariant"
     * )
     */
    private $promotionBanners;

    public function __construct()
    {
        parent::__construct();
        $this->promotionBanners = new ArrayCollection();
    }

    protected function createTranslation(): ProductVariantTranslationInterface
    {
        return new ProductVariantTranslation();
    }

    /**
     * @return Collection|PromotionBanner[]
     */
    public function getPromotionBanners(): Collection
    {
        return $this->promotionBanners;
    }

    public function addPromotionBanner(PromotionBanner $promotionBanner): self
    {
        if (!$this->promotionBanners->contains($promotionBanner)) {
            $this->promotionBanners[] = $promotionBanner;
            $promotionBanner->setProductVariant($this);
        }

        return $this;
    }

    public function removePromotionBanner(PromotionBanner $promotionBanner): self
    {
        if ($this->promotionBanners->contains($promotionBanner)) {
            $this->promotionBanners->removeElement($promotionBanner);
            // set the owning side to null (unless already changed)
            if ($promotionBanner->getProductVariant() === $this) {
                $promotionBanner->setProductVariant(null);
            }
        }

        return $this;
    }
}
