<?php

namespace App\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Sylius\Bundle\OrderBundle\Form\Type\CartType;
use Symfony\Component\Form\AbstractTypeExtension;

class CartTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->remove('shippingAddress')
            ->remove('billingAddress')
        ;
    }

    public function getExtendedTypes() {
        return [CartType::class];
    }
}
