<?php

namespace App\Form\Admin;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Location $location */
        $location = $options['data'];

        $builder
            ->add('name', null, [
                'label' => 'app.ui.name',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'app.ui.address',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3])
                ]
            ])
            ->add('phoneNumber', null, [
                'label' => 'app.ui.phone_number',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('extension', null, [
                'label' => 'app.ui.phone_number_extension'
            ])
            ->add('photoType', FileType::class, [
                'label' => 'app.ui.image',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => ($location->getPhoto()) ? $location->getPhoto() : 'app.ui.choose_file',
                ],
                'help' => 'app.ui.location.image_help',
                'constraints' => [
                    new Image([
                        'minWidth' => '296',
                        'maxWidth' => '296',
                        'minHeight' => '216',
                        'maxHeight' => '216',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ]
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
}
