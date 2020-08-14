<?php

namespace App\Form\AdminApi;

use App\Entity\Promotion\PromotionCoupon;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class CouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $code = $options['code'] ?? null;

        $builder
            ->add('code', null, [
                'constraints' => [
                    new NotBlank(['groups' => ['all']]),
                    new Length(['max' => 15])
                ],
                'empty_data' => $code
            ])
            ->add('description', null, [
                'constraints' => [
                    new Length(['min' => 5, 'max' => 500])
                ]
            ])
            ->add('enabled', BooleanType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    PromotionCoupon::TYPE_PERCENTAGE => PromotionCoupon::TYPE_PERCENTAGE,
                    PromotionCoupon::TYPE_FIXED_AMOUNT => PromotionCoupon::TYPE_FIXED_AMOUNT
                ],
                'constraints' => [
                    new NotBlank(['groups' => ['creation']])
                ]
            ])
            ->add('amount', NumberType::class, [
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                    new Range(['min' => 0, 'max' => 999])
                ],
            ])
            ->add('oneUsagePerUser', BooleanType::class)
            ->add('limitUsageToXQuantityOfUsers', BooleanType::class)
            ->add('usageLimit', NumberType::class, [
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                    new Range(['min' => 0, 'max' => 999])
                ],
            ])
            ->add('startsAt', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd  HH:mm',
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                    new GreaterThan(['value' => 'now']),
                    new DateTime()
                ],
            ])
            ->add('endsAt', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd  HH:mm',
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                    new GreaterThan(['value' => 'now']),
                    new DateTime()
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'code' => null,
        ]);
    }
}
