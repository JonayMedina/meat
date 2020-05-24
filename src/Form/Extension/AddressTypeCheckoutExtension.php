<?php

namespace App\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\AddressType;

class AddressTypeCheckoutExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('scheduledDate', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'app.ui.checkout.form.scheduled_date',
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'DD/MM/YY'
                ]
            ])
            ->add('preferredTime', ChoiceType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'app.ui.checkout.order.preferred_time',
                'choices' => [
                    'app.ui.checkout.order.preferred_time.first' => 1,
                    'app.ui.checkout.order.preferred_time.second' => 2,
                    'app.ui.checkout.order.preferred_time.third' => 3,
                ],
                'data' => 'sylius.gender.female',
                'multiple' => false,
                'expanded' => true
            ]);
    }

    public function getExtendedTypes() {
        return [AddressType::class];
    }
}
