<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ORM\Table(name="app_faq")
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="App\Repository\FAQRepository")
 */
class FAQ implements ResourceInterface
{
    use TimestampableTrait, BlameableTrait;

    const TYPE_QUESTION = 'question';

    const TYPE_SCHEDULE = 'schedule';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @var int
     * @Serializer\Expose()
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 1;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="question", type="string", length=125, nullable=false)
     */
    private $question;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="answer", type="string", length=200, nullable=true)
     */
    private $answer;

    /**
     * @var array
     * @Serializer\Expose()
     * @ORM\Column(name="time_to_place_an_order", type="json", nullable=true)
     */
    private $timeToPlaceAnOrder;

    /**
     * @var array
     * @Serializer\Expose()
     * @ORM\Column(name="order_delivery_time", type="json", nullable=true)
     */
    private $orderDeliveryTime;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="type", type="string", length=50, nullable=false)
     */
    private $type = self::TYPE_QUESTION;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return FAQ
     */
    public function setPosition(?int $position): FAQ
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * @param string $question
     * @return FAQ
     */
    public function setQuestion(?string $question): FAQ
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     * @return FAQ
     */
    public function setAnswer(?string $answer): FAQ
    {
        $this->answer = $answer;

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
     * @return FAQ
     */
    public function setType(?string $type): FAQ
    {
        if (!in_array($type, [self::TYPE_QUESTION, self::TYPE_SCHEDULE])) {
            throw new BadRequestHttpException('Invalid FAQ type.');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return array
     */
    public function getTimeToPlaceAnOrder(): ?array
    {
        return $this->timeToPlaceAnOrder;
    }

    /**
     * @param array $timeToPlaceAnOrder
     * @return FAQ
     */
    public function setTimeToPlaceAnOrder(?array $timeToPlaceAnOrder): FAQ
    {
        if ($timeToPlaceAnOrder[0]['start'] == ''  && $timeToPlaceAnOrder[0]['end'] == '' && $timeToPlaceAnOrder[1]['start'] == '' && $timeToPlaceAnOrder[1]['end'] == '' && $timeToPlaceAnOrder[2]['start'] == '' && $timeToPlaceAnOrder[2]['end'] == '') {
            $this->timeToPlaceAnOrder = [];

            return $this;
        }

        $this->timeToPlaceAnOrder = $timeToPlaceAnOrder;

        return $this;
    }

    /**
     * @return array
     */
    public function getOrderDeliveryTime(): ?array
    {
        return $this->orderDeliveryTime;
    }

    /**
     * @param array $orderDeliveryTime
     * @return FAQ
     */
    public function setOrderDeliveryTime(?array $orderDeliveryTime): FAQ
    {
        if ($orderDeliveryTime[0]['name'] == '' && $orderDeliveryTime[0]['start'] == ''  && $orderDeliveryTime[0]['end'] == '' && $orderDeliveryTime[1]['name'] == '' && $orderDeliveryTime[1]['start'] == '' && $orderDeliveryTime[1]['end'] == '') {
            $this->orderDeliveryTime = [];

            return $this;
        }

        $this->orderDeliveryTime = $orderDeliveryTime;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->question;
    }
}
