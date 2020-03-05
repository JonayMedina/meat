<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Sylius\Component\Core\Model\Promotion as BasePromotion;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_promotion")
 */
class Promotion extends BasePromotion
{
    private $codeAlreadyInUse = false;

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     * @param $payload
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->codeAlreadyInUse) {
            $context->buildViolation('app.ui.coupon_code_is_already_in_use')
                ->atPath('code')
                ->addViolation();
        }
    }

    /**
     * @param bool $codeAlreadyInUse
     */
    public function setCodeAlreadyInUse(bool $codeAlreadyInUse): void
    {
        $this->codeAlreadyInUse = $codeAlreadyInUse;
    }
}
