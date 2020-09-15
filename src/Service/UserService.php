<?php

namespace App\Service;

use App\Entity\User\ShopUser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserService
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $JWTManager;

    /**
     * UserService constructor.
     * @param JWTTokenManagerInterface $JWTManager
     */
    public function __construct(JWTTokenManagerInterface $JWTManager)
    {
        $this->JWTManager = $JWTManager;
    }

    /**
     * Generate JWT for given user.
     * @param ShopUser $user
     * @return string
     */
    public function generateJWT(ShopUser $user)
    {
        return $this->JWTManager->create($user);
    }
}
