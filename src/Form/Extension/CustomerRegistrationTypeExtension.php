<?php


namespace App\Form\Extension;


use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerRegistrationType;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;

class CustomerRegistrationTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Adding new fields works just like in the parent form type.
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
            ->add('birthday', BirthdayType::class, [
                'label' => 'sylius.form.customer.birthday',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('termsAccepted', CheckboxType::class, array(
                'label' => 'app.ui.accept_the',
                'mapped' => false,
                'constraints' => new IsTrue(),
            ));
    }

    public static function getExtendedTypes(): iterable
    {
        return [CustomerRegistrationType::class];
    }
}
