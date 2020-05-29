<?php

namespace App\Form\Extension;

use Faker\Provider\Text;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sylius\Bundle\CustomerBundle\Form\Type\CustomerProfileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerProfileTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->remove('gender')
            ->remove('birthday')
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'label' => 'sylius.form.customer.gender',
                'choices' => [
                    CustomerInterface::FEMALE_GENDER => CustomerInterface::FEMALE_GENDER,
                    CustomerInterface::MALE_GENDER => CustomerInterface::MALE_GENDER,
                ],
                'empty_data' => CustomerInterface::UNKNOWN_GENDER,
                'data' => 'sylius.gender.female',
                'multiple' => false,
                'expanded' => true
            ])
            ->add('birthday', TextType::class, [
                'label' => 'sylius.form.customer.birthday',
                'attr' => [
                    'class' => 'datepicker',
                    'type' => 'text',
                    'placeholder' => 'DD/MM/YY'
                ],
                'required' => false,
            ])
            ->add('address', AddressType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false
                ]
            );
    }

    public static function getExtendedTypes(): iterable
    {
        return [CustomerProfileType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'allow_extra_fields' => true
        ]);
    }
}
