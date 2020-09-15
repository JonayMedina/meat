<?php

namespace App\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Sylius\Bundle\OrderBundle\Form\Type\CartType;
use Symfony\Component\Form\AbstractTypeExtension;

/**
 * Class CartTypeExtension
 * @package App\Form\Extension
 */
class CartTypeExtension extends AbstractTypeExtension
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->remove('shippingAddress')
            ->remove('billingAddress')
        ;
    }

    /**
     * @return iterable|string[]
     */
    public function getExtendedTypes() {
        return [CartType::class];
    }
}
