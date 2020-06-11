<?php

namespace App\Form\Admin;

use App\Entity\Segment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Positive;

class SegmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'app.ui.segment.name',
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 50])
                ],
                'attr' => [
                    'maxlength' => 50
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'app.ui.gender.label',
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'app.ui.segment.gender.'.CustomerInterface::FEMALE_GENDER => CustomerInterface::FEMALE_GENDER,
                    'app.ui.segment.gender.'.CustomerInterface::MALE_GENDER => CustomerInterface::MALE_GENDER,
                ],
                'data' => 'app.ui.segment.gender.'.CustomerInterface::FEMALE_GENDER
            ])
            ->add('frequencyType', ChoiceType::class, [
                'label' => 'app.ui.frequency',
                'expanded' => true,
                'choices' => [
                    'app.ui.segment.frequency_type.'.Segment::TYPE_PURCHASE_TIMES.'.label' => Segment::TYPE_PURCHASE_TIMES,
                    'app.ui.segment.frequency_type.'.Segment::TYPE_FIXED_AMOUNT.'.label' => Segment::TYPE_FIXED_AMOUNT,
                ]
            ])
            ->add('fixedAmount', NumberType::class, [
                'label' => 'app.ui.segment.frequency_type.amount',
                'constraints' => [
                    new Positive(),
                    new Length(['max' => 4])
                ],
                'attr' => [
                    'maxlength' => 4,
                    'class' => 'integer-only'
                ]
            ])
            ->add('purchaseTimes', NumberType::class, [
                'label' => 'app.ui.segment.frequency_type.times',
                'constraints' => [
                    new Positive(),
                    new Length(['max' => 2])
                ],
                'attr' => [
                    'maxlength' => 2,
                    'class' => 'integer-only'
                ]
            ])
            ->add('minAge', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Length(['max' => 2])
                ],
                'attr' => [
                    'maxlength' => 2,
                    'class' => 'integer-only'
                ]
            ])
            ->add('maxAge', TextType::class, [
                'label' => false,
                'constraints' => [
                    new Length(['max' => 2])
                ],
                'attr' => [
                    'maxlength' => 2,
                    'class' => 'integer-only'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Segment::class,
        ]);
    }
}
