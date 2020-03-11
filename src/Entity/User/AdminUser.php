<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_admin_user")
 */
class AdminUser extends BaseAdminUser
{
    use BlameableTrait, IpTraceableTrait;

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if (!empty($this->getFirstName())) {
            $fullName = $this->getFirstName() . ' ' . $this->getLastName();
            return (string)$fullName;
        }

        return $this->getUsername();
    }

}
