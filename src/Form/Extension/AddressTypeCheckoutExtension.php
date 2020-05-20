<?php

namespace App\Form\Extension;

use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerGuestType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\AddressType;
use Symfony\Component\Validator\Constraints\Valid;

class AddressTypeCheckoutExtension extends AbstractTypeExtension
{
    public function getExtendedTypes() {
        return [AddressType::class];
    }
}
