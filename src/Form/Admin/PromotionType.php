<?php

namespace App\Form\Admin;

use App\Entity\Promotion\Promotion;
use App\Entity\Promotion\PromotionCoupon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Promotion $promotion */
        $promotion = $options['data'];

        /** @var PromotionCoupon $coupon */
        $coupon = $promotion ? $promotion->getCoupons()[0] : null;

        $channel = $options['channel'];

        if ($channel == null) {
            throw new BadRequestHttpException('You must pass a channel to promotion form type.');
        }

        $builder
            ->add('code', null, [
                'label' => 'app.ui.coupon_code',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 15])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'app.ui.coupon_description',
                'constraints' => [
                    new Length(['min' => 5, 'max' => 500])
                ]
            ])
            ->add('startsAt', null, [
                'label' => 'app.ui.coupon_starts_at',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy',
                'time_label' => 'Hora',
                'constraints' => [
                    new GreaterThan(['value' => 'now']),
                    new DateTime()
                ],
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('endsAt', null, [
                'label' => 'app.ui.coupon_ends_at',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'dd/MM/yyyy',
                'time_label' => 'Hora',
                'constraints' => [
                    new GreaterThan(['value' => 'now']),
                    new DateTime()
                ],
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'app.ui.coupon_type',
                'choices' => [
                    'app.ui.coupon_' . PromotionCoupon::TYPE_PERCENTAGE => PromotionCoupon::TYPE_PERCENTAGE,
                    'app.ui.coupon_' .PromotionCoupon::TYPE_FIXED_AMOUNT => PromotionCoupon::TYPE_FIXED_AMOUNT
                ],
                'mapped' => false,
                'data' =>  $coupon ? $coupon->getTypeSlug($channel->getCode()) : null,
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('amount', NumberType::class, [
                'label' => 'app.ui.coupon_amount',
                'mapped' => false,
                'data' =>  $coupon ? $coupon->getValue($channel->getCode()) : null,
                'constraints' => [
                    new NotBlank(),
                    new Range(['min' => 0, 'max' => 999])
                ],
                'attr' => [
                    'maxlength' => 3
                ]
            ])
            ->add('oneUsagePerUser', CheckboxType::class, [
                'label' => 'app.ui.coupon_one_usage_per_user',
                'mapped' => false,
                'required' => false,
                'data' => ($coupon && $coupon->getPerCustomerUsageLimit() != PromotionCoupon::MAX_USAGES_PER_USER) ? true : false,
            ])
            ->add('limitUsageToXQuantityOfUsers', CheckboxType::class, [
                'label' => 'app.ui.limit_usage_to_x_quantity_of_users',
                'mapped' => false,
                'required' => false,
                'data' => ($coupon && $coupon->getUsageLimit()) ? true : false
            ])
            ->add('usageLimit', NumberType::class, [
                'label' => 'app.ui.usage_limit',
                'mapped' => false,
                'required' => false,
                'data' => $coupon ? $coupon->getUsageLimit() : null,
                'constraints' => [
                    new Range(['min' => 0, 'max' => 9999])
                ],
                'attr' => [
                    'class' => 'width-100',
                    'maxlength' => 4
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
            'channel' => null
        ]);
    }
}
