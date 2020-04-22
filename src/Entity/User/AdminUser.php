<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 * @ORM\Table(name="sylius_admin_user")
 */
class AdminUser extends BaseAdminUser
{
    use BlameableTrait, IpTraceableTrait;

    /**
     * Can create new users.
     */
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * Cannot create new users.
     */
    const ROLE_EDITOR = 'ROLE_EDITOR';

    /**
     * @var \DateTimeInterface|null
     * @ORM\Column(name="last_password_update", type="datetime", nullable=true)
     */
    private $lastPasswordUpdate;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true, unique=true)
     */
    private $passwordRecoveryId;

    public function getFullName()
    {
        if (!empty($this->getFirstName())) {
            $fullName = $this->getFirstName() . ' ' . $this->getLastName();
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

    /**
     * @param array $roles
     * @return AdminUser
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastPasswordUpdate(): ?\DateTimeInterface
    {
        return $this->lastPasswordUpdate;
    }

    /**
     * @param \DateTimeInterface|null $lastPasswordUpdate
     * @return AdminUser
     */
    public function setLastPasswordUpdate(?\DateTimeInterface $lastPasswordUpdate): AdminUser
    {
        $this->lastPasswordUpdate = $lastPasswordUpdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordRecoveryId(): ?string
    {
        return $this->passwordRecoveryId;
    }

    /**
     * @param string $passwordRecoveryId
     * @return AdminUser
     */
    public function setPasswordRecoveryId(?string $passwordRecoveryId): AdminUser
    {
        $this->passwordRecoveryId = $passwordRecoveryId;
        return $this;
    }
}
