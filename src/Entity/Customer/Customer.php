<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use App\Entity\Addressing\Address;
use App\Model\BlameableTrait;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_customer")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Customer extends BaseCustomer
{
    use BlameableTrait;

    /**
     * @var Address
     *
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Addressing\Address",
     *     inversedBy="customer",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="default_billing_address_id",
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    private $defaultBillingAddress;

    /**
     * @return Address
     */
    public function getDefaultBillingAddress(): ?Address
    {
        return $this->defaultBillingAddress;
    }

    /**
     * @param Address $defaultBillingAddress
     * @return Customer
     */
    public function setDefaultBillingAddress(?Address $defaultBillingAddress): Customer
    {
        $this->defaultBillingAddress = $defaultBillingAddress;

        return $this;
    }
}
