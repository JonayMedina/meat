<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TokenPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $code = $options['code'];

        $builder
            ->add('token', null, [
                'label' => 'app.ui.forgot_password_token',
                'constraints' => [
                    new NotBlank(['message' => 'app.ui.forgot_password.token_field_not_blank']),
                    new Length(['max' => 10])
                ],
                'attr' => [
                    'maxlength' => 10
                ],
                'data' => $code
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'code' => null
        ]);
    }
}
