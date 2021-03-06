<?php

namespace App\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class AddressTypeExtension
 * @package App\Form\Extension
 */
class AddressTypeExtension extends AbstractTypeExtension
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
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
            ->add('annotations', TextareaType::class, [
                'required' => true,
                'label' => 'app.form.address.ask_for',
                'attr' => [
                    'class' => 'annotations'
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

    /**
     * @return iterable|string[]
     */
    public function getExtendedTypes() {
        return [AddressType::class];
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'allow_extra_fields' => true
        ]);
    }
}
