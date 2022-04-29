<?php

declare(strict_types=1);

namespace App\View\ShopApi;

class CustomerView
{
    /** @var int */
    public $id;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $email;

    /** @var \DateTimeInterface|null */
    public $birthday;

    /** @var string */
    public $gender;

    /** @var string|null */
    public $phoneNumber;

    /** @var bool */
    public $subscribedToNewsletter;

    /** @var bool|null */
    public $termsAccepted;

    /** @var \DateTimeInterface|null */
    public $termsAcceptedAt;
}
