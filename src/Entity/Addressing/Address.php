<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use DateTime;
use App\Model\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sylius\Component\Core\Model\Address as BaseAddress;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_address")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="firstName",
 *          column=@ORM\Column(
 *              nullable = true,
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="lastName",
 *          column=@ORM\Column(
 *              nullable = true,
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="street",
 *          column=@ORM\Column(
 *              nullable = true,
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="city",
 *          column=@ORM\Column(
 *              nullable = true,
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="postcode",
 *          column=@ORM\Column(
 *              nullable = true,
 *          )
 *      ),
 *      @ORM\AttributeOverride(name="countryCode",
 *          column=@ORM\Column(
 *              nullable = true,
 *          )
 *      )
 * })
 */
class Address extends BaseAddress
{
    use BlameableTrait;

    const STATUS_PENDING = 'pending';

    const STATUS_VALIDATED = 'validated';

    const STATUS_REJECTED = 'rejected';

    const TYPE_SHIPPING = 'shipping';

    const TYPE_BILLING = 'billing';

    /**
     * @var string
     * @ORM\Column(name="full_address", type="text", nullable=true)
     * @Assert\NotBlank(groups={"app_address"})
     */
    private $fullAddress;

    /**
     * @var string
     * @ORM\Column(name="annotations", type="text", nullable=true)
     * @Assert\NotBlank(groups={"app_address"})
     */
    private $annotations;

    /**
     * @var string
     * @ORM\Column(name="tax_id", type="string", length=100, nullable=true)
     */
    private $taxId;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=100)
     */
    private $type = self::TYPE_SHIPPING;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=100, nullable=true)
     */
    private $status = self::STATUS_PENDING;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedAt;

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

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Address
     */
    public function setStatus(?string $status): Address
    {
        if (!in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_VALIDATED,
            self::STATUS_REJECTED,
        ])) {
            throw new BadRequestHttpException('Invalid status for Address.');
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Mark address as validated.
     * @return Address
     */
    public function validate(): self
    {
        $this->status = self::STATUS_VALIDATED;
        $this->validatedAt = new DateTime();

        return $this;
    }

    /**
     * Reject an address.
     * @return Address
     */
    public function reject(): self
    {
        $this->status = self::STATUS_REJECTED;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getValidatedAt(): ?DateTime
    {
        return $this->validatedAt;
    }

    /**
     * @param DateTime $validatedAt
     * @return Address
     */
    public function setValidatedAt(?DateTime $validatedAt): Address
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Address
     */
    public function setType(?string $type): Address
    {
        if (!in_array($type, [
            self::TYPE_BILLING,
            self::TYPE_SHIPPING,
        ])) {
            throw new BadRequestHttpException('Invalid type for address.');
        }

        $this->type = $type;

        return $this;
    }
}
