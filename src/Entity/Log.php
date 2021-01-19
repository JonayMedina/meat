<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\TimestampableTrait;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity()
 * @ORM\Table(name="app_log")
 * @Serializer\ExclusionPolicy("all")
 */
class Log
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(type="string", length=150, nullable=false)
     */
    private $method;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $uri;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $contentType;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $query;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(type="text", nullable=true)
     */
    private $response;

    /**
     * @var int
     * @Serializer\Expose()
     * @ORM\Column(type="integer", nullable=true)
     */
    private $statusCode;

    /**
     * @var string
     * @Serializer\Expose()
     * @ORM\Column(name="`order`", type="text", nullable=true)
     */
    private $order;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return Log
     */
    public function setMethod(string $method): Log
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return Log
     */
    public function setUri(string $uri): Log
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param ?string $content
     * @return Log
     */
    public function setContent(?string $content): Log
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * @param ?string $contentType
     * @return Log
     */
    public function setContentType(?string $contentType): Log
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getQuery(): ?string
    {
        return $this->query;
    }

    /**
     * @param ?string $query
     * @return Log
     */
    public function setQuery(?string $query): Log
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     * @param ?string $response
     * @return Log
     */
    public function setResponse(?string $response): Log
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return ?int
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @param ?int $statusCode
     * @return Log
     */
    public function setStatusCode(?int $statusCode): Log
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getOrder(): ?string
    {
        return $this->order;
    }

    /**
     * @param ?string $order
     * @return Log
     */
    public function setOrder(?string $order): Log
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->method;
    }
}
