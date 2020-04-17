<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Favorite;
use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Core\Model\ShopUser as BaseShopUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_shop_user")
 */
class ShopUser extends BaseShopUser
{
    use BlameableTrait, IpTraceableTrait;

    const ADDRESS_LIMIT = 3;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Favorite",
     *     mappedBy="shopUser"
     * )
     */
    private $favorites;

    /**
     * @var \DateTime
     * @ORM\Column(name="terms_and_conditions_accepted_at", type="datetime", nullable=true)
     */
    private $termsAndConditionsAcceptedAt;

    public function __construct()
    {
        parent::__construct();
        $this->favorites = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getTermsAndConditionsAcceptedAt(): \DateTime
    {
        return $this->termsAndConditionsAcceptedAt;
    }

    /**
     * @param \DateTime $termsAndConditionsAcceptedAt
     */
    public function setTermsAndConditionsAcceptedAt(\DateTime $termsAndConditionsAcceptedAt): void
    {
        $this->termsAndConditionsAcceptedAt = $termsAndConditionsAcceptedAt;
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
            $favorite->setShopUser($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): self
    {
        if ($this->favorites->contains($favorite)) {
            $this->favorites->removeElement($favorite);
            // set the owning side to null (unless already changed)
            if ($favorite->getShopUser() === $this) {
                $favorite->setShopUser(null);
            }
        }

        return $this;
    }
}
