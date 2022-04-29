<?php

namespace App\Form\Admin;

use App\Entity\AboutStore;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

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

            ->add('facebookUrl', null, [
                'label' => 'app.ui.facebook_url',
                'constraints' => [
                    new Url()
                ]
            ])
            ->add('twitterUrl', null, [
                'label' => 'app.ui.twitter_url',
                'constraints' => [
                    new Url()
                ]
            ])
            ->add('instagramUrl', null, [
                'label' => 'app.ui.instagram_url',
                'constraints' => [
                    new Url()
                ]
            ])
            ->add('pinterestUrl', null, [
                'label' => 'app.ui.pinterest_url',
                'constraints' => [
                    new Url()
                ]
            ])
            ->add('appStoreUrl', null, [
                'label' => 'app.ui.app_store_url',
                'constraints' => [
                    new Url()
                ]
            ])
            ->add('playStoreUrl', null, [
                'label' => 'app.ui.play_store_url',
                'constraints' => [
                    new Url()
                ]
            ])
            ->add('complaintsEmail', null, [
                'label' => 'app.ui.complaints_email',
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            ->add('contactEmail', null, [
                'label' => 'app.ui.contact_email',
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            ->add('phoneNumber', null, [
                'label' => 'app.ui.phone_number',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8, 'max' => 12])
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
