<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order")
 */
class Order extends BaseOrder
{
    const MIN_RATING = 0;

    const MAX_RATING = 5;

    /**
     * @var int $rating
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rating;

    /**
     * @return int
     */
    public function getRating(): ?int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating(?int $rating): void
    {
        $this->rating = $rating;
    }

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->rating < self::MIN_RATING) {
            $context->buildViolation('La calificación debe ser de al menos {{ limit }} estrellas')
                ->atPath('rating')
                ->addViolation();
        }

        if ($this->rating > self::MAX_RATING) {
            $context->buildViolation('La calificación no puede ser mayor a {{ limit }} estrellas')
                ->atPath('rating')
                ->addViolation();
        }
    }
}
