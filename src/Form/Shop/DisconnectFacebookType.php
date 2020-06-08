<?php

namespace App\Form\Shop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * Class ChangeEmailType
 * @package App\Form\Shop
 */
class DisconnectFacebookType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'app.ui.form.disconnect.email',
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('confirmEmail', EmailType::class, [
                'label' => 'app.ui.form.disconnect.confirm_email',
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'app.ui.form.disconnect.password',
                'constraints' => [
                    new NotBlank(),
                    New Length(['min' => 9, 'max' => 20, 'minMessage' => 'app.ui.password.min_length'])
                ]
            ])
        ;
    }
}
