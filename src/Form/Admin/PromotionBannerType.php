<?php

namespace App\Form\Admin;

use App\Entity\PromotionBanner;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PromotionBannerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var PromotionBanner $banner */
        $banner = $options['data'];

        $builder
            ->add('name', null, [
                'label' => 'app.ui.name',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('productVariant', null, [
                'label' => 'app.ui.banners_product_if_applicable',
                'placeholder' => 'app.ui.banner_no_product_selected'
            ])
            ->add('startDate', null, [
                'label' => 'app.ui.start_date',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'time_label' => 'Hora',
                'constraints' => [
                    new GreaterThan(['value' => 'now']),
                    new DateTime(),
                    new NotBlank()
                ]
            ])
            ->add('endDate', null, [
                'label' => 'app.ui.end_date',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'time_label' => 'Hora',
                'constraints' => [
                    new GreaterThan(['value' => 'now']),
                    new DateTime(),
                    new NotBlank()
                ]
            ])
            ->add('photoWebType', FileType::class, [
                'label' => 'app.ui.banners.web',
                'mapped' => false,
                'required' => false,
                'help' => 'app.ui.banner_web.image_help',
                'attr' => [
                    'placeholder' => ($banner->getPhotoWeb()) ? $banner->getPhotoWeb() : 'app.ui.choose_file',
                ],
                'constraints' => [
                    new Image([
                        'minWidth' => '1440',
                        'maxWidth' => '1440',
                        'minHeight' => '500',
                        'maxHeight' => '500',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ]
                    ])
                ],
            ])
            ->add('photoTabletType', FileType::class, [
                'label' => 'app.ui.banners.tablet',
                'mapped' => false,
                'required' => false,
                'help' => 'app.ui.banner_tablet.image_help',
                'attr' => [
                    'placeholder' => ($banner->getPhotoTablet()) ? $banner->getPhotoTablet() : 'app.ui.choose_file',
                ],
                'constraints' => [
                    new Image([
                        'minWidth' => '767',
                        'maxWidth' => '767',
                        'minHeight' => '350',
                        'maxHeight' => '350',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ]
                    ])
                ],
            ])
            ->add('photoMobileType', FileType::class, [
                'label' => 'app.ui.banners.mobile',
                'mapped' => false,
                'required' => false,
                'help' => 'app.ui.banner_mobile.image_help',
                'attr' => [
                    'placeholder' => ($banner->getPhotoMobile()) ? $banner->getPhotoMobile() : 'app.ui.choose_file',
                ],
                'constraints' => [
                    new Image([
                        'minWidth' => '320',
                        'maxWidth' => '320',
                        'minHeight' => '296',
                        'maxHeight' => '296',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ]
                    ])
                ],
            ])
            ->add('photoAppType', FileType::class, [
                'label' => 'app.ui.banners.app',
                'mapped' => false,
                'required' => false,
                'help' => 'app.ui.banner_app.image_help',
                'attr' => [
                    'placeholder' => ($banner->getPhotoApp()) ? $banner->getPhotoApp() : 'app.ui.choose_file',
                ],
                'constraints' => [
                    new Image([
                        'minWidth' => '882',
                        'maxWidth' => '882',
                        'minHeight' => '399',
                        'maxHeight' => '399',
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
            'data_class' => PromotionBanner::class,
        ]);
    }
}
