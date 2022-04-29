<?php

namespace App\Entity;

use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use App\Model\BlameableTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use App\Repository\NotificationRepository;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ORM\Table(name="app_notification")
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification implements ResourceInterface
{
    use TimestampableTrait, BlameableTrait;

    const TYPE_PROMOTION = 'promotion';

    const TYPE_INFO = 'info';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    public $title;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    public $text;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    public $type;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $seen = false;

    /**
     * @var array
     * @ORM\Column(type="json", nullable=true)
     */
    private $response;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\PushNotification",
     *     inversedBy="notifications"
     * )
     * @ORM\JoinColumn(
     *     name="push_notification_id",
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    private $pushNotification;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User\ShopUser",
     *     inversedBy="notifications"
     * )
     * @ORM\JoinColumn(
     *     name="shop_user_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\Order\Order",
     *     inversedBy="notifications"
     * )
     * @ORM\JoinColumn(
     *     name="order_id",
     *     referencedColumnName="id",
     *     nullable=true
     * )
     */
    private $order;

    /**
     * Notification constructor.
     * @param $pushNotification
     * @param $user
     * @param string $title
     * @param string $text
     * @param string $type
     */
    public function __construct(PushNotification $pushNotification = null, ShopUser $user = null, string $title = null, string $text = null, string $type = null)
    {
        $this->pushNotification = $pushNotification;
        $this->user = $user;
        $this->title = $title;
        $this->text = $text;
        $this->type = $type;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Notification
     */
    public function setTitle(?string $title): Notification
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Notification
     */
    public function setText(?string $text): Notification
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Notification
     */
    public function setType(?string $type): Notification
    {
        if (!in_array($type, [
            self::TYPE_PROMOTION,
            self::TYPE_INFO
        ])) {
            throw new BadRequestHttpException('Invalid type for Notification.');
        }


        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSeen(): ?bool
    {
        return $this->seen;
    }

    /**
     * @param bool $seen
     * @return Notification
     */
    public function setSeen(?bool $seen): Notification
    {
        $this->seen = $seen;

        return $this;
    }

    /**
     * @return array
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }

    /**
     * @param array $response
     * @return Notification
     */
    public function setResponse(?array $response): Notification
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return PushNotification
     */
    public function getPushNotification()
    {
        return $this->pushNotification;
    }

    /**
     * @param mixed $pushNotification
     * @return Notification
     */
    public function setPushNotification($pushNotification)
    {
        $this->pushNotification = $pushNotification;

        return $this;
    }

    /**
     * @return ShopUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return Notification
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getSeen(): ?bool
    {
        return $this->seen;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }
}
