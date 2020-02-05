<?php

namespace App\Message;

/**
 * Class Sync
 * @package App\Message
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class Sync
{
    /**
     * On create a new entity.
     */
    const TYPE_PERSIST = 'persist';

    /**
     * On update an existing entity.
     */
    const TYPE_UPDATE = 'update';

    /**
     * On remove entity.
     */
    const TYPE_REMOVE = 'remove';

    /**
     * Order/Cart model.
     */
    const MODEL_ORDER = 'order';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $model;

    /**
     * @var array
     */
    private $content;

    /**
     * @var array
     */
    private $diff;

    /**
     * Sync constructor.
     * @param string $type
     * @param string $model
     * @param array $content
     * @param array $diff
     */
    public function __construct(string $type, string $model, array $content, array $diff = [])
    {
        $this->type = $type;
        $this->model = $model;
        $this->content = $content;
        $this->diff = $diff;
    }

    /**
     * Return model
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Return type
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Return content.
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Return diff.
     * @return array
     */
    public function getDiff(): array
    {
        return $this->diff;
    }
}
