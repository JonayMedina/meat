<?php

namespace App\Form\Admin;

use App\Entity\Segment;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SegmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'app.ui.segment.name',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'app.ui.gender.label',
                'expanded' => true,
                'choices' => [
                    'app.ui.segment.gender.both' => '',
                    'app.ui.segment.gender.'.CustomerInterface::FEMALE_GENDER => CustomerInterface::FEMALE_GENDER,
                    'app.ui.segment.gender.'.CustomerInterface::MALE_GENDER => CustomerInterface::MALE_GENDER,
                ]
            ])
            ->add('frequencyType', ChoiceType::class, [
                'label' => 'app.ui.frequency',
                'expanded' => true,
                'choices' => [
                    'app.ui.segment.frequency_type.'.Segment::TYPE_PURCHASE_TIMES.'.label' => Segment::TYPE_PURCHASE_TIMES,
                    'app.ui.segment.frequency_type.'.Segment::TYPE_FIXED_AMOUNT.'.label' => Segment::TYPE_FIXED_AMOUNT,
                ]
            ])
            ->add('fixedAmount', IntegerType::class, [
                'label' => 'app.ui.segment.frequency_type.amount'
            ])
            ->add('purchaseTimes', IntegerType::class, [
                'label' => 'app.ui.segment.frequency_type.times'
            ])
            ->add('minAge', null, [
                'label' => false
            ])
            ->add('maxAge', null, [
                'label' => false
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
