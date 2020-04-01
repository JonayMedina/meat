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
     * Taxon model.
     */
    const MODEL_CATEGORY = 'category';

    /**
     * Product/pricing model
     */
    const MODEL_PRODUCT = 'product';

    /**
     * Coupon model.
     */
    const MODEL_COUPON = 'coupon';

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
     * Sync constructor.
     * @param string $type
     * @param string $model
     * @param int $id
     */
    public function __construct(string $type, string $model, int $id)
    {
        $this->id = $id;
        $this->type = $type;
        $this->model = $model;

        // TODO: figure out how to add API URL here...
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
}
