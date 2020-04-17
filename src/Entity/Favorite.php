<?php

namespace App\Entity;

use App\Entity\User\ShopUser;
use App\Model\BlameableTrait;
use App\Entity\Product\Product;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Table(name="app_favorite")
 * @ORM\Entity(repositoryClass="App\Repository\FavoriteRepository")
 */
class Favorite implements ResourceInterface
{
    use BlameableTrait, TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User\ShopUser",
     *     inversedBy="favorites"
     * )
     * @ORM\JoinColumn(
     *     name="shop_user_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private $shopUser;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Product\Product",
     *     inversedBy="favorites"
     * )
     * @ORM\JoinColumn(
     *     name="product_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private $product;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ShopUser|null
     */
    public function getShopUser(): ?ShopUser
    {
        return $this->shopUser;
    }

    /**
     * @param ShopUser|null $shopUser
     * @return $this
     */
    public function setShopUser(?ShopUser $shopUser): self
    {
        $this->shopUser = $shopUser;

        return $this;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product|null $product
     * @return $this
     */
    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
