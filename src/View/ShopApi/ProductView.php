<?php

declare(strict_types=1);

namespace App\View\ShopApi;

class ProductView extends \Sylius\ShopApiPlugin\View\Product\ProductView
{
    /** @var array */
    public $measurementUnit = [];

    /** @var bool */
    public $isFavorite = false;
}
