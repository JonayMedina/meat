<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Table(name="app_terms_and_conditions")
 * @ORM\Entity(repositoryClass="App\Repository\TermsAndConditionsRepository")
 */
class TermsAndConditions implements ResourceInterface
{
    use TimestampableTrait, BlameableTrait, IpTraceableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $text;

    public function getId(): ?int
    {
        return $this->id;
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
     */
    public function setText(?string $text): void
    {
        $this->text = $text;
    }
}
