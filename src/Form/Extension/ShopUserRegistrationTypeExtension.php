<?php

namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Sylius\Bundle\CoreBundle\Form\Type\User\ShopUserRegistrationType;

/**
 * Class ShopUserRegistrationTypeExtension
 * @package App\Form\Extension
 */
class ShopUserRegistrationTypeExtension extends AbstractTypeExtension
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'validation_groups' => 'sylius_shop_user_registration'
        ]);
    }

    /**
     * @return iterable
     */
    public static function getExtendedTypes(): iterable
    {
        return [ShopUserRegistrationType::class];
    }
}
