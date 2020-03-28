<?php


namespace App\Form\Extension;


use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\CustomerBundle\Form\Type\CustomerProfileType;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerProfileTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->remove('gender')
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
            ->add('defaultAddress', AddressType::class, [
                'required' => false,
                'label' => false
                ]
            );
    }

    public static function getExtendedTypes(): iterable
    {
        return [CustomerProfileType::class];
    }
}
