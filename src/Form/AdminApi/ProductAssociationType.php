<?php

namespace App\Form\AdminApi;

use App\Entity\Product\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductAssociationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('similar_products', EntityType::class, [
                'class' => Product::class,
                'choice_value' => 'code',
                'multiple' => true
            ])
            ->add('complimentary_products', EntityType::class, [
                'class' => Product::class,
                'choice_value' => 'code',
                'multiple' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
