<?php

namespace App\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Sylius\Bundle\UserBundle\Form\Type\UserChangePasswordType;

/**
 * Class UserChangePasswordTypeExtension
 * @package App\Form\Extension
 */
class UserChangePasswordTypeExtension extends AbstractTypeExtension
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->remove('newPassword')
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'sylius.form.user_change_password.new'],
                'second_options' => ['label' => 'app.form.user_change_password.confirmation'],
                'invalid_message' => 'sylius.user.plainPassword.mismatch',
            ]);
    }

    /**
     * @return iterable|string[]
     */
    public function getExtendedTypes()
    {
        return [UserChangePasswordType::class];
    }
}
