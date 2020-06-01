<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User\ShopUser;
use App\Entity\PushNotification;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Repository\AddressRepositoryInterface;

/**
 * AddressService Class
 * @author Rormdar Zavala <rzavala@praga.ws>
 */
class AddressService
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * AddressService constructor.
     * @param AddressRepositoryInterface $addressRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(AddressRepositoryInterface $addressRepository, EntityManagerInterface $entityManager)
    {
        $this->addressRepository = $addressRepository;
        $this->entityManager = $entityManager;
    }

    public function reject(Address $address)
    {
        $address->setStatus(Address::STATUS_REJECTED);
        $address->setValidatedAt(null);

        /**
         * Send notification here...
         * @var Customer $customer
         * @var ShopUser $user
         */
        $customer = $address->getCustomer();
        $user = $customer->getUser();

        if ($user instanceof ShopUser) {
            $notification = new Notification(null, $user, 'Lo sentimos', 'Por el momento no podemos brindarte nuestro servicio en esta área.', PushNotification::TYPE_ADDRESS_REJECTED);
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }
    }

    public function validate(Address $address)
    {
        $address->setStatus(Address::STATUS_VALIDATED);
        $address->setValidatedAt(new \DateTime());

        /**
         * Send notification here...
         * @var Customer $customer
         * @var ShopUser $user
         */
        $customer = $address->getCustomer();
        $user = $customer->getUser();

        if ($user instanceof ShopUser) {
            $notification = new Notification(null, $user, '¡Felicitaciones!', 'Se ha validado tu dirección de envío.', PushNotification::TYPE_ADDRESS_VALIDATED);
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }
    }

}
