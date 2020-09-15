<?php

declare(strict_types=1);

namespace App\Factory\ShopApi;

use App\Entity\User\ShopUser;
use App\View\ShopApi\CustomerView;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\ShopApiPlugin\Factory\Customer\CustomerViewFactoryInterface;

final class CustomerViewFactory implements CustomerViewFactoryInterface
{
    /** @var string */
    private $customerViewClass;

    public function __construct(string $customerViewClass)
    {
        $this->customerViewClass = $customerViewClass;
    }

    /** {@inheritdoc} */
    public function create(CustomerInterface $customer)
    {
        /** @var CustomerView $customerView */
        $customerView = new $this->customerViewClass();

        /** @var ShopUser $user */
        $user = $customer->getUser();

        $customerView->id = $customer->getId();
        $customerView->firstName = $customer->getFirstName();
        $customerView->lastName = $customer->getLastName();
        $customerView->email = $customer->getEmail();
        $customerView->birthday = $customer->getBirthday();
        $customerView->gender = $customer->getGender();
        $customerView->phoneNumber = $customer->getPhoneNumber();
        $customerView->subscribedToNewsletter = $customer->isSubscribedToNewsletter();
        $customerView->termsAccepted = (bool)$user->getTermsAndConditionsAcceptedAt();
        $customerView->termsAcceptedAt = $user->getTermsAndConditionsAcceptedAt();

        return $customerView;
    }
}
