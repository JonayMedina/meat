<?php

namespace App\Form\Admin;

use App\Entity\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


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
                    new NotBlank(),
                    new Length(['max' => 35])
                ]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'app.ui.address',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3, 'max' => 90])
                ]
            ])
            ->add('phoneNumber', null, [
                'label' => 'app.ui.phone_number',
                'constraints' => [
                    new NotBlank(),
                    new Regex(['pattern' => '/^\d{4}[\s.-]\d{4}$/', 'message' => 'app.ui.admin.not_valid_phone']),
                    new Length(['max' => 9])
                ]
            ])
            ->add('extension', NumberType::class, [
                'label' => 'app.ui.phone_number_extension',
                'constraints' => [
                    new Positive(),
                    new Length(['max' => 4])
                ]
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
