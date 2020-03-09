<?php

namespace App\Form\Admin;

use App\Entity\AboutStore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AboutStoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('aboutUs', null, [
                'label' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 10])
                ],
                'attr' => [
                    'class' => 'summernote'
                ]
            ])
            ->add('phrase', TextareaType::class, [
                'label' => 'app.ui.about_procasa_phrase',
                'constraints' => [
                    new Length(['min' => 5, 'max' => 125])
                ],
                'attr' => [
                    'maxlength' => 125,
                    'class' => 'input-counter'
                ]
            ])
            ->add('author', null, [
                'label' => 'app.ui.about_procasa_phrase_author',
                'constraints' => [
                    new Length(['min' => 2, 'max' => 30])
                ],
                'attr' => [
                    'class' => 'input-counter'
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
