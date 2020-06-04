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
     * Order checkout completed.
     */
    const TYPE_ORDER_CHECKOUT_COMPLETED = 'order_completed';

    /**
     * Order has been rated.
     */
    const TYPE_ORDER_RATED = 'order_rated';

    /**
     * Order/Cart model.
     */
    const MODEL_ORDER = 'order';

    /**
     * Addressing/Address entity.
     */
    const MODEL_ADDRESS = 'address';

    /**
     * Customer model.
     */
    const MODEL_CUSTOMER = 'customer';

    /** @var int $id */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $metadata = [];

    /**
     * Sync constructor.
     * @param string $type
     * @param string $model
     * @param int $id
     * @param string $url
     * @param array $metadata
     */
    public function __construct(string $type, string $model, int $id, string $url = '', array $metadata = [])
    {
        $this->id = $id;
        $this->type = $type;
        $this->model = $model;
        $this->url = $url;
        $this->metadata = $metadata;
    }

    /**
     * Return model id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
