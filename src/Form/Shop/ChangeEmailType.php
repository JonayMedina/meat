<?php

namespace App\Form\Shop;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

/**
 * Class ChangeEmailType
 * @package App\Form\Shop
 */
class ChangeEmailType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'app.ui.form.current_email',
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('newEmail', EmailType::class, [
                'label' => 'app.ui.form.new_email',
                'constraints' => [
                    new NotBlank(),
                    new Email()
                ]
            ])
        ;
    }
}
