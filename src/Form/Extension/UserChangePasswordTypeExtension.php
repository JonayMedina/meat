<?php


namespace App\Form\Extension;


use Sylius\Bundle\UserBundle\Form\Type\UserChangePasswordType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

class UserChangePasswordTypeExtension extends AbstractTypeExtension
{
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

    public function getExtendedTypes()
    {
        return [UserChangePasswordType::class];
    }
}
