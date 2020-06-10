<?php

namespace App\Form\Admin;

use App\Entity\AboutStore;
use App\Validator\Constraints\NotDecimal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class PurchaseSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('maximumPurchaseValue', NumberType::class, [
                'label' => 'app.ui.purchase_settings_max_purchase',
                'constraints' => [
                    new NotBlank(),
                    new NotDecimal(),
                    new Positive(),
                    new Range(['min' => 1, 'max' => 99999])
                ],
                'attr' => [
                    'maxlength' => 5
                ]
            ])
            ->add('minimumPurchaseValue', NumberType::class, [
                'label' => 'app.ui.purchase_settings_min_purchase',
                'constraints' => [
                    new NotBlank(),
                    new NotDecimal(),
                    new Positive(),
                    new Range(['min' => 1, 'max' => 999])
                ],
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('daysToChooseInAdvanceToPurchase', NumberType::class, [
                'label' => 'app.ui.purchase_settings_days_to_choose_in_advance_to_purchase',
                'constraints' => [
                    new NotBlank(),
                    new NotDecimal(),
                    new Positive(),
                    new Range(['min' => 1, 'max' => 99])
                ],
                'attr' => [
                    'maxlength' => 2
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AboutStore::class,
        ]);
    }
}
