<?php

declare(strict_types=1);

namespace App\View\ShopApi;

class AddressView  extends \Sylius\ShopApiPlugin\View\AddressBook\AddressView
{
    /** @var string */
    public $askFor;

    /** @var string */
    public $fullAddress;

    /** @var array */
    public $parent;
}
