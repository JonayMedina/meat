<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Favorite;
use App\Entity\ShopUserDevice;
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

    const SHIPPING_ADDRESS_LIMIT = 3;

    const BILLING_ADDRESS_LIMIT = 1;

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

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\ShopUserDevice",
     *     mappedBy="user"
     * )
     */
    private $devices;

    public function __construct()
    {
        parent::__construct();
        $this->favorites = new ArrayCollection();
        $this->devices = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getTermsAndConditionsAcceptedAt(): ?\DateTime
    {
        return $this->termsAndConditionsAcceptedAt;
    }

    /**
     * @param \DateTime $termsAndConditionsAcceptedAt
     */
    public function setTermsAndConditionsAcceptedAt(?\DateTime $termsAndConditionsAcceptedAt): void
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

    /**
     * @return Collection|ShopUserDevice[]
     */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(ShopUserDevice $device): self
    {
        if (!$this->devices->contains($device)) {
            $this->devices[] = $device;
            $device->setUser($this);
        }

        return $this;
    }

    public function removeDevice(ShopUserDevice $device): self
    {
        if ($this->devices->contains($device)) {
            $this->devices->removeElement($device);
            // set the owning side to null (unless already changed)
            if ($device->getUser() === $this) {
                $device->setUser(null);
            }
        }

        return $this;
    }

    public function getFullName()
    {
        if (!empty($this->getCustomer()->getFirstName())) {
            $fullName = $this->getCustomer()->getFirstName(). ' ' . $this->getCustomer()->getLastName();
            return (string)$fullName;
        }

        return $this->getUsername();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getFullName();
    }
}
