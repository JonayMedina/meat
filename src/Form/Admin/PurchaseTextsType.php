<?php

namespace App\Form\Admin;

use App\Entity\AboutStore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PurchaseTextsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstPurchaseMessage', null, [
                'label' => false,
                'attr' => [
                    'class' => 'input-counter height-200',
                    'maxlength' => 90,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'app.ui.admin.purchase.not_empty']),
                    new Length(['min' => 5, 'max' => 90])
                ]
            ])

            ->add('newAddressMessage', null, [
                'label' => false,
                'attr' => [
                    'class' => 'input-counter height-200',
                    'maxlength' => 90
                ],
                'constraints' => [
                    new NotBlank(['message' => 'app.ui.admin.purchase.not_empty']),
                    new Length(['min' => 5, 'max' => 90])
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
