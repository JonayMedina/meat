<?php

namespace App\Form\Admin;

use App\Entity\User\AdminUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var AdminUser $adminUser */
        $adminUser = $options['data'];

        $builder
            ->add('firstName', null, [
                'label' => 'app.ui.first_name',
                'attr' => [
                    'maxlength' => 25
                ],
                'constraints' => [
                    new Length(['max' => 25]),
                    new NotBlank()
                ]
            ])
            ->add('lastName', null, [
                'label' => 'app.ui.last_name',
                'attr' => [
                    'maxlength' => 25
                ],
                'constraints' => [
                    new Length(['max' => 25]),
                    new NotBlank()
                ]
            ])
            ->add('email', null, [
                'label' => 'app.ui.email',
                'attr' => [
                    'maxlength' => 50
                ],
                'constraints' => [
                    new Length(['max' => 50]),
                    new NotBlank(),
                    new Email()
                ]
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'app.ui.role',
                'choices' => [
                    'app.ui.roles.'.strtolower(AdminUser::ROLE_EDITOR) => AdminUser::ROLE_EDITOR,
                    'app.ui.roles.'.strtolower(AdminUser::ROLE_ADMIN) => AdminUser::ROLE_ADMIN,
                    'app.ui.roles.'.strtolower(AdminUser::ROLE_ADMIN_API) => AdminUser::ROLE_ADMIN_API,
                ],
                'mapped' => false,
                'data' => $adminUser->getRoles()[0]
            ]);

        /** Will be shown only for new users */
        if (!$adminUser || null === $adminUser->getId()) {
            $builder
                ->add('temporalPassword', PasswordType::class, [
                    'label' => 'app.ui.temporal_password',
                    'attr' => [
                        'maxlength' => 20
                    ],
                    'mapped' => false,
                    'constraints' => [
                        new Length(['min' => 8, 'max' => 20, 'minMessage' => 'app.ui.error.password_must_be_at_least_8_characters']),
                        new NotBlank()
                    ]
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdminUser::class,
        ]);
    }
}
