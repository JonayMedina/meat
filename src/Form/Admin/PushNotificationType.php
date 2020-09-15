<?php

namespace App\Form\Admin;

use App\Entity\PushNotification;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PushNotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $now = date('Y-m-d H:i:s');

        $builder
            ->add('title', null, [
                'label' => 'app.ui.push_title',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 50])
                ],
                'attr' => [
                    'maxlength' => 50
                ]
            ])
            ->add('description', null, [
                'label' => 'app.ui.push_description',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 250])
                ],
                'attr' => [
                    'maxlength' => 250
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'app.ui.push_type',
                'expanded' => true,
                'choices' => [
                    'app.ui.push.types.'.PushNotification::TYPE_PROMOTION => PushNotification::TYPE_PROMOTION,
                    'app.ui.push.types.'.PushNotification::TYPE_INFO => PushNotification::TYPE_INFO,
                ],
            ])
            ->add('promotionType', ChoiceType::class, [
                'label' => 'app.ui.promotion_type',
                'expanded' => true,
                'choices' => [
                    'app.ui.push.promotion_types.'.PushNotification::PROMOTION_TYPE_COUPON => PushNotification::PROMOTION_TYPE_COUPON,
                    'app.ui.push.promotion_types.'.PushNotification::PROMOTION_TYPE_BANNER => PushNotification::PROMOTION_TYPE_BANNER,
                ],
            ])
            ->add('promotionCoupon', null, [
                'label' => 'app.ui.coupon',
                'placeholder' => 'app.ui.select_a_coupon',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('promotion_coupon')
                        ->andWhere('promotion_coupon.enabled = :enabled')
                        ->setParameter('enabled', true);
                },
                'attr' => [
                    'class' => 'select2'
                ],
            ])
            ->add('promotionBanner', null, [
                'label' => 'app.ui.promotion_banner',
                'placeholder' => 'app.ui.select_a_coupon',
                'query_builder' => function (EntityRepository $er) use ($now) {
                    return $er->createQueryBuilder('promotion_banner')
                        ->andWhere('promotion_banner.startDate <= :now')
                        ->andWhere('promotion_banner.endDate >= :now')
                        ->setParameter('now', $now);
                },
            ])
            ->add('segment', null, [
                'label' => 'app.ui.segment_label',
                'placeholder' => 'app.ui.all_segments',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PushNotification::class,
        ]);
    }
}
