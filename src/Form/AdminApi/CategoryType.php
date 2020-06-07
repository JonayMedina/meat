<?php

namespace App\Form\AdminApi;

use JBZoo\Image\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $code = $options['code'] ?? null;

        $builder
            ->add('code', null, [
                'constraints' => [
                    new NotBlank(),
                ],
                'empty_data' => $code
            ])
            ->add('name', null, [
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('description', null, [
                'required' => false,
                'constraints' => [
                    new Length(['min' => 5]),
                ]
            ])
            ->add('parent', null, [

            ])
            ->add('photo', null, [
                'constraints' => [
                    new Callback([$this, 'validatePhoto']),
                ]
            ])
            ->add('left', null, [

            ])
            ->add('right', null, [

            ])
            ->add('position', null, [

            ]);
    }

    public function validatePhoto($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $data = $form->getData();
        $base64 = $data['photo'];
        $message = 'The format for image should be base64.';

        try {
            (new Image($base64))
                ->getImage();
        } catch (\ErrorException $e) {
            $context
                ->buildViolation($message)
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'code' => null
        ]);
    }
}
