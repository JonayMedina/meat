<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @ORM\Table(name="app_faq")
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
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 1;

    /**
     * @var string
     * @ORM\Column(name="question", type="string", length=125, nullable=false)
     */
    private $question;

    /**
     * @var string
     * @ORM\Column(name="answer", type="string", length=200, nullable=false)
     */
    private $answer;

    /**
     * @var string
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
}
