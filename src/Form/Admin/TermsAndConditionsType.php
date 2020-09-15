<?php

namespace App\Form\Admin;

use App\Entity\TermsAndConditions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TermsAndConditionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', null, [
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 50])
                ],
                'attr' => [
                    'class' => 'summernote',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TermsAndConditions::class,
        ]);
    }
}
