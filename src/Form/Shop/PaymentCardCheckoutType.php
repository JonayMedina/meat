<?php

namespace App\Form\Shop;

use App\Entity\Order\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PaymentCardCheckoutType
 * @package App\Form\Shop
 */
class PaymentCardCheckoutType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('number', TextType::class, [
                'label' => 'app.ui.form.payment.number',
                'mapped' => false
            ])
            ->add('name', TextType::class, [
                'label' => 'app.ui.form.payment.name',
                'mapped' => false
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'app.ui.form.payment.expiration_date',
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'MM/YY'
                ],
                'mapped' => false
            ])
            ->add('cvv', NumberType::class, [
                'label' => 'app.ui.form.payment.cvv',
                'mapped' => false
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
