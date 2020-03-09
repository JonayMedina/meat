<?php

namespace App\Entity;

use App\Model\BlameableTrait;
use App\Model\IpTraceableTrait;
use App\Model\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Table(name="about_store")
 * @ORM\Entity(repositoryClass="App\Repository\AboutStoreRepository")
 */
class AboutStore implements ResourceInterface
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
     * @ORM\Column(name="about_us", type="text", nullable=true)
     */
    private $aboutUs;

    /**
     * @var string
     * @ORM\Column(name="phrase", type="string", length=125, nullable=true)
     */
    private $phrase;

    /**
     * @var string
     * @ORM\Column(name="author", type="string", length=30, nullable=true)
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAboutUs(): ?string
    {
        return $this->aboutUs;
    }

    /**
     * @param string $aboutUs
     * @return AboutStore
     */
    public function setAboutUs(?string $aboutUs): self
    {
        $this->aboutUs = $aboutUs;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhrase(): ?string
    {
        return $this->phrase;
    }

    /**
     * @param string $phrase
     * @return AboutStore
     */
    public function setPhrase(?string $phrase): self
    {
        $this->phrase = $phrase;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @param string $author
     * @return AboutStore
     */
    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }
}
