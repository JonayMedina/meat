<?php

namespace App\Model\Queue;

class Body
{
    private $id;

    private $model;

    private $type;

    private $url;

    /**
     * Body constructor.
     * @param $id
     * @param $model
     * @param $type
     * @param $url
     */
    public function __construct($id, $model, $type, $url)
    {
        $this->id = $id;
        $this->model = $model;
        $this->type = $type;
        $this->url = $url;
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
}
