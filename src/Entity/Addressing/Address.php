<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use App\Model\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_address")
 */
class Address extends BaseAddress
{
    use BlameableTrait;

    /**
     * @var string
     * @ORM\Column(name="full_address", type="text", nullable=true)
     */
    private $fullAddress;

    /**
     * @var string
     * @ORM\Column(name="annotations", type="text", nullable=true)
     */
    private $annotations;

    /**
     * @var string
     * @ORM\Column(name="tax_id", type="string", length=100, nullable=true)
     */
    private $taxId;

    /**
     * @return string
     */
    public function getAnnotations(): ?string
    {
        return $this->annotations;
    }

    /**
     * @param string $annotations
     * @return Address
     */
    public function setAnnotations(string $annotations): self
    {
        $this->annotations = $annotations;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullAddress(): ?string
    {
        return $this->fullAddress;
    }

    /**
     * @param string $fullAddress
     * @return Address
     */
    public function setFullAddress(string $fullAddress): self
    {
        $this->fullAddress = $fullAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    /**
     * @param string $taxId
     * @return Address
     */
    public function setTaxId(?string $taxId): self
    {
        $this->taxId = $taxId;

        return $this;
    }
}
