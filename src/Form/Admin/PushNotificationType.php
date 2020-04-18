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
            ->add('promotionCoupon', null, [
                'label' => 'app.ui.coupon',
                'placeholder' => 'app.ui.select_a_coupon',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('promotion_coupon')
                        ->andWhere('promotion_coupon.enabled = :enabled')
                        ->setParameter('enabled', true);
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
