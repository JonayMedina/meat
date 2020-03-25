<?php


namespace App\Form\Extension;


use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\AdminApiBundle\Form\Type\CustomerProfileType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;

class CustomerProfileTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('termsAccepted', CheckboxType::class, array(
                'label' => 'app.ui.accept_the',
                'mapped' => false,
                'constraints' => new IsTrue()
            ));
    }

    public static function getExtendedTypes(): iterable
    {
        return [CustomerProfileType::class];
    }
}
