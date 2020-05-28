<?php

namespace App\Form\Shop;

use App\Entity\Customer\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;

class BillingProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('defaultBillingAddress', AddressType::class, [
                'validation_groups' => ['app_billing'],
                'constraints' => [
                    new Valid(),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
