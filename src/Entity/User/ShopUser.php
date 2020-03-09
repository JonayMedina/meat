<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShopUser as BaseShopUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_shop_user")
 */
class ShopUser extends BaseShopUser
{
    /**
     * @var \DateTime
     * @ORM\Column(name="terms_and_conditions_accepted_at", type="datetime", nullable=true)
     */
    private $termsAndConditionsAcceptedAt;

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
}
