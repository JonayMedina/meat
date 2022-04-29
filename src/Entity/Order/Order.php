<?php

declare(strict_types=1);

namespace App\Entity\Order;

use DateTime;
use App\Entity\Notification;
use App\Model\BlameableTrait;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Customer\Customer;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Component\Validator\Constraints as Assert;
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

    const STATUS_PENDING = 'pending';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_DELIVERED = 'delivered';

    const SORT_RECENT = 'recent';

    const SORT_ORDER_NUMBER = 'order_number';

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
     * @var DateTime $estimatedDeliveryDate
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $estimatedDeliveryDate;

    /**
     * @var string $preferredDeliveryTime
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $preferredDeliveryTime;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $scheduledDeliveryDate;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Notification",
     *     mappedBy="order",
     *     orphanRemoval=true
     * )
     */
    private $notifications;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ratingNotificationSent = false;

    public function __construct()
    {
        parent::__construct();
        $this->notifications = new ArrayCollection();
    }

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
     * @Assert\Callback()
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

    /**
     * Return Meat House Order Status.
     */
    public function getStatus(): string
    {
        if ($this->getShippingState() == ShipmentInterface::STATE_SHIPPED) {
            return self::STATUS_DELIVERED;
        }

        if ($this->getState() == OrderInterface::STATE_NEW) {
            return self::STATUS_PENDING;
        }

        if ($this->getState() == OrderInterface::STATE_CANCELLED) {
            return self::STATUS_CANCELLED;
        }

        return '---';
    }

    /**
     * Return customer name.
     * @return string|null
     */
    public function getCustomerName(): ?string
    {
        $customer = $this->getCustomer();

        if ($customer instanceof Customer) {
            if ($customer->getFirstName() && $customer->getLastName()) {
                return $customer->getFirstName() . " " . $customer->getLastName();
            } else {
                return $customer->getEmail();
            }
        } else {
            return null;
        }
    }

    /**
     * @return DateTime
     */
    public function getEstimatedDeliveryDate(): ?DateTime
    {
        return $this->estimatedDeliveryDate;
    }

    /**
     * @param DateTime $estimatedDeliveryDate
     * @return Order
     */
    public function setEstimatedDeliveryDate(?DateTime $estimatedDeliveryDate): Order
    {
        $this->estimatedDeliveryDate = $estimatedDeliveryDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getPreferredDeliveryTime(): ?string
    {
        return $this->preferredDeliveryTime;
    }

    /**
     * @param string $preferredDeliveryTime
     * @return Order
     */
    public function setPreferredDeliveryTime(?string $preferredDeliveryTime): Order
    {
        $this->preferredDeliveryTime = $preferredDeliveryTime;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeliverTime(): ?string
    {
        return $this->getPreferredDeliveryTime();
    }

    /**
     * @return string|null
     */
    public function getDeliverDate(): ?string
    {
        if (null == $this->getEstimatedDeliveryDate()) {
            return null;
        }

        // TODO: Remove locale logic from here...
        setlocale(LC_ALL,"es_ES");

        return strftime('%A %e de %B %Y', (int)$this->getEstimatedDeliveryDate()->format('U'));
    }

    /**
     * @return DateTime|null
     */
    public function getScheduledDeliveryDate(): ?DateTime
    {
        return $this->scheduledDeliveryDate;
    }

    /**
     * @param DateTime $scheduledDeliveryDate
     * @return ORder
     */
    public function setScheduledDeliveryDate(?DateTime $scheduledDeliveryDate): Order
    {
        $this->scheduledDeliveryDate = $scheduledDeliveryDate;

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setOrder($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getOrder() === $this) {
                $notification->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isRatingNotificationSent(): ?bool
    {
        return $this->ratingNotificationSent;
    }

    /**
     * @param bool $ratingNotificationSent
     * @return Order
     */
    public function setRatingNotificationSent(?bool $ratingNotificationSent): Order
    {
        $this->ratingNotificationSent = $ratingNotificationSent;

        return $this;
    }
}
