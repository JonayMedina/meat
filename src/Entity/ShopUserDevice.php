<?php

namespace App\Entity;

use App\Entity\User\ShopUser;
use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use App\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ORM\Table(name="app_shop_user_device")
 * @ORM\Entity(repositoryClass="App\Repository\ShopUserDeviceRepository")
 */
class ShopUserDevice implements ResourceInterface
{
    const TYPE_WEB = 'web';

    const TYPE_ANDROID = 'android';

    const TYPE_IOS = 'ios';

    use BlameableTrait, TimestampableTrait, IpTraceableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $key;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotBlank()
     */
    private $type = self::TYPE_WEB;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(
     *     targetEntity="App\Entity\User\ShopUser",
     *     inversedBy="devices"
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return ShopUserDevice
     */
    public function setKey(?string $key): ShopUserDevice
    {
        $this->key = $key;

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
     * @return ShopUserDevice
     */
    public function setType(?string $type): ShopUserDevice
    {
        if (!in_array($type, [
            self::TYPE_WEB,
            self::TYPE_ANDROID,
            self::TYPE_IOS,
        ])) {
            throw new BadRequestHttpException('Bad type for user\'s device');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return ShopUser|null
     */
    public function getUser(): ?ShopUser
    {
        return $this->user;
    }

    /**
     * @param ShopUser|null $user
     * @return $this
     */
    public function setUser(?ShopUser $user): self
    {
        $this->user = $user;

        return $this;
    }
}
