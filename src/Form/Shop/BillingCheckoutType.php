<?php

namespace App\Form\Shop;

use App\Entity\Addressing\Address;
use App\Entity\Order\Order;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BillingCheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('billingAddress', AddressType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
