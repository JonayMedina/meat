<?php

namespace App\Form\Shop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * Class ChangeEmailType
 * @package App\Form\Shop
 */
class AddPasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'app.ui.form.add_password.password',
                'constraints' => [
                    new NotBlank(),
                    New Length(['min' => 9, 'max' => 20, 'minMessage' => 'app.ui.password.min_length'])
                ]
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'app.ui.form.add_password.confirm_password',
                'constraints' => [
                    new NotBlank(),
                    New Length(['min' => 9, 'max' => 20, 'minMessage' => 'app.ui.password.min_length'])
                ]
            ])
        ;
    }
}
