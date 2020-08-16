<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\User\Model\UserOAuth as BaseUserOAuth;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_user_oauth")
 */
class UserOAuth extends BaseUserOAuth
{
    /**
     * @var boolean
     * @ORM\Column(name="is_verified", type="boolean", nullable=true)
     */
    private $isVerified;

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * @param bool $isVerified
     * @return UserOAuth
     */
    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
