<?php

namespace App\Model\Queue;

class Body
{
    private $id;

    private $model;

    private $type;

    private $url;

    /**
     * @var array
     */
    private $metadata = [];

    /**
     * Body constructor.
     * @param $id
     * @param $model
     * @param $type
     * @param $url
     * @param array $metadata
     */
    public function __construct($id, $model, $type, $url, $metadata = [])
    {
        $this->id = $id;
        $this->model = $model;
        $this->type = $type;
        $this->url = $url;
        $this->metadata = $metadata;
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
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
