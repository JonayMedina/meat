<?php

declare(strict_types=1);

namespace App\Entity\Order;

use App\Model\BlameableTrait;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_order")
 * @Cache("NONSTRICT_READ_WRITE")
 */
class Order extends BaseOrder
{
    use BlameableTrait;

    const MIN_RATING = 0;

    const MAX_RATING = 5;

    /**
     * @var int $rating
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rating;

    /**
     * @var string $ratingComment
     * @ORM\Column(type="text", nullable=true)
     */
    private $ratingComment;

    /**
     * @return int
     */
    public function getRating(): ?int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     * @return Order
     */
    public function setRating(?int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return string
     */
    public function getRatingComment(): ?string
    {
        return $this->ratingComment;
    }

    /**
     * @param string $ratingComment
     * @return Order
     */
    public function setRatingComment(?string $ratingComment): self
    {
        $this->ratingComment = $ratingComment;

        return $this;
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
