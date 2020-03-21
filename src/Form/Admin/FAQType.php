<?php

namespace App\Form\Admin;

use App\Entity\FAQ;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class FAQType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question', null, [
                'label' => 'app.ui.question',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 125])
                ],
                'attr' => [
                    'maxlength' => 125,
                    'class' => 'input-counter'
                ]
            ])
            ->add('answer', TextareaType::class, [
                'label' => 'app.ui.answer',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 5, 'max' => 200])
                ],
                'attr' => [
                    'maxlength' => 200,
                    'class' => 'input-counter'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FAQ::class,
        ]);
    }
}
