<?php

namespace App\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AddressTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->remove('firstName')
            ->remove('lastName')
            ->remove('company')
            ->remove('countryCode')
            ->remove('street')
            ->remove('city')
            ->remove('postcode')
            ->remove('phoneNumber')
            ->add('fullAddress', TextareaType::class, [
                'required' => true,
                'label' => 'app.form.address.full_address',
                'attr' => [
                    'placeholder' => 'app.ui.address.full_address.instructions'
                ]
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'app.form.address.annotations'
            ])
            ->add('phoneNumber', TextType::class, [
                'required' => true,
                'label' => 'app.form.address.phone_number',
            ])
            ->add('taxId', TextType::class, [
                'required' => false,
                'label' => 'app.ui.checkout.billing.tax_id',
            ])
        ;
    }

    public function getExtendedTypes() {
        return [AddressType::class];
    }
}
