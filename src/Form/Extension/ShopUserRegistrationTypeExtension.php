<?php

namespace App\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Sylius\Bundle\CoreBundle\Form\Type\User\ShopUserRegistrationType;

/**
 * Class ShopUserRegistrationTypeExtension
 * @package App\Form\Extension
 */
class ShopUserRegistrationTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('plainPassword')
            // Adding new fields works just like in the parent form type.
            ->add('plainPassword', PasswordType::class, [
                'label' => 'sylius.form.user.password.label'
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'validation_groups' => 'sylius_shop_user_registration'
        ]);
    }

    /**
     * @return iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [ShopUserRegistrationType::class];
    }
}
