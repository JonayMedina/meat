<?php

namespace App\Model\Queue;

class Response
{
    private $id;

    private $body;

    private $createdAt;

    /**
     * Response constructor.
     * @param $id
     * @param $body
     * @param $createdAt
     */
    public function __construct($id, $body, $createdAt)
    {
        $this->id = $id;
        $this->body = $body;
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
