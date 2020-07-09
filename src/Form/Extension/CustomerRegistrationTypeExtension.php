<?php

namespace App\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Validator\Constraints\IsTrue;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Sylius\Bundle\CoreBundle\Form\Type\Customer\CustomerRegistrationType;

/**
 * Class CustomerRegistrationTypeExtension
 * @package App\Form\Extension
 */
class CustomerRegistrationTypeExtension extends AbstractTypeExtension
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
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
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'DD/MM/YY'
                ],
                'required' => false,
            ])
            ->add('termsAccepted', CheckboxType::class, array(
                'label' => 'app.ui.accept_the',
                'mapped' => false,
                'constraints' => new IsTrue()
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'validation_groups' => ['sylius_user_registration', 'sylius']
        ]);
    }

    /**
     * @return iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [CustomerRegistrationType::class];
    }
}
