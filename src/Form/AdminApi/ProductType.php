<?php

namespace App\Form\AdminApi;

use JBZoo\Image\Image;
use App\Entity\Taxonomy\Taxon;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', null, [
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                ]
            ])
            ->add('name', null, [
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                ]
            ])
            ->add('description', null, [
                'constraints' => [
                    new Length(['min' => 5]),
                ]
            ])
            ->add('price', NumberType::class, [
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                ]
            ])
            ->add('offerPrice', NumberType::class, [

            ])
            ->add('measurementUnit', CollectionType::class, [
                'allow_add' => true,
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                ]
            ])
            ->add('keywords', null, [

            ])
            ->add('photo', null, [
                'constraints' => [
                    new NotBlank(['groups' => ['creation']]),
                    new Callback([$this, 'validatePhoto']),
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Taxon::class,
                'choice_value' => 'code',
                'multiple' => false
            ])
            ->add('categories', EntityType::class, [
                'class' => Taxon::class,
                'choice_value' => 'code',
                'multiple' => true
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
        ]);
    }
}
