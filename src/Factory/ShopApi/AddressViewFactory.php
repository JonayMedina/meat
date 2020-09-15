<?php

declare(strict_types=1);

namespace App\Factory\ShopApi;

use App\Entity\Addressing\Address;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\ShopApiPlugin\View\AddressBook\AddressView;
use Sylius\ShopApiPlugin\Factory\AddressBook\AddressViewFactoryInterface;

final class AddressViewFactory implements AddressViewFactoryInterface
{
    /** @var string */
    private $addressViewClass;

    public function __construct(string $addressViewClass)
    {
        $this->addressViewClass = $addressViewClass;
    }

    /** {@inheritdoc} */
    public function create(AddressInterface $address): AddressView
    {
        /** @var \App\View\ShopApi\AddressView $addressView */
        $addressView = new $this->addressViewClass();
        $parentObject = [];

        if ($address->getParent()) {
            $parentObject = [
                'id' => $address->getParent()->getId(),
                'full_address' => $address->getParent()->getFullAddress(),
                'annotations' => $address->getParent()->getFirstName() ? $address->getParent()->getFirstName() : $address->getParent()->getAnnotations(),
            ];
        }

        /** @var Address $address */
        $addressView->phoneNumber = $address->getPhoneNumber();
        $addressView->askFor = $address->getFirstName() ? $address->getFirstName() : $address->getAnnotations();
        $addressView->fullAddress = $address->getFullAddress();
        $addressView->parent = $parentObject;

        return $addressView;
    }
}
