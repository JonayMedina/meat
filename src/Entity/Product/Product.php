<?php

declare(strict_types=1);

namespace App\Entity\Product;

use App\Entity\Favorite;
use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct
{
    use BlameableTrait, IpTraceableTrait;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Favorite",
     *     mappedBy="product"
     * )
     */
    private $favorites;

    public function __construct()
    {
        parent::__construct();
        $this->favorites = new ArrayCollection();
    }

    /**
     * @return Collection|Favorite[]
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites[] = $favorite;
            $favorite->setProduct($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): self
    {
        if ($this->favorites->contains($favorite)) {
            $this->favorites->removeElement($favorite);
            // set the owning side to null (unless already changed)
            if ($favorite->getProduct() === $this) {
                $favorite->setProduct(null);
            }
        }

        return $this;
    }

    public function getMeasurementUnit()
    {
        if (!$this->hasVariants()) {
            return null;
        }

        return $this->getVariants()[0]->getMeasurementUnit();
    }

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}
