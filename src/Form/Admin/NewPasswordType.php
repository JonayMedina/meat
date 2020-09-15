<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'app.ui.first_login.password_must_match',
            'options' => ['attr' => ['class' => 'password-field']],
            'first_options'  => [
                'label' => 'app.ui.first_login.password',
                'constraints' => [
                    new NotBlank(['message' => 'app.ui.first_login.enter_password']),
                    new Length(['min' => 8, 'max' => 20])
                ]
            ],
            'second_options' => [
                'label' => 'app.ui.first_login.repeat_your_password',
                'constraints' => [
                    new NotBlank(['message' => 'app.ui.first_login.enter_password']),
                    new Length(['min' => 8, 'max' => 20])
                ]
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
