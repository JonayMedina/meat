<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use App\Entity\Notification;
use App\Entity\User\ShopUser;
use App\Entity\PushNotification;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SenderInterface
     */
    private $sender;

    /**
     * AddressService constructor.
     * @param AddressRepositoryInterface $addressRepository
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param SenderInterface $sender
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        SenderInterface $sender
    ) {
        $this->addressRepository = $addressRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->sender = $sender;
    }

    /**
     * @param Address $address
     */
    public function reject(Address $address): void
    {
        $address->setStatus(Address::STATUS_REJECTED);
        $address->setValidatedAt(new \DateTime());

        /**
         * Send notification here...
         * @var Customer $customer
         * @var ShopUser $user
         */
        $customer = $address->getCustomer();

        if (!$customer instanceof Customer) {
            return;
        }

        $user = $customer->getUser();

        if ($user instanceof ShopUser) {
            $this->sender->send('rejected_address', [$user->getEmail()], ['customer' => $user->getCustomer(), 'address' => $address]);
            $notification = new Notification(null, $user, 'Lo sentimos', 'Por el momento no podemos brindarte nuestro servicio en esta ??rea.', PushNotification::TYPE_ADDRESS_REJECTED);
            $this->entityManager->persist($notification);

            /** Find children and enable those addresses. */
            foreach ($this->findChildrenAddresses($address) as $childrenAddress) {
                $this->reject($childrenAddress);
            }

        } else {
            $parentAddress = $this->findParentAddress($address);

            if ($parentAddress instanceof Address) {
                $this->reject($parentAddress);

                /** Find children and enable those addresses. */
                foreach ($this->findChildrenAddresses($parentAddress) as $childrenAddress) {
                    $this->reject($childrenAddress);
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param Address $address
     */
    public function validate(Address $address): void
    {
        $address->setStatus(Address::STATUS_VALIDATED);
        $address->setValidatedAt(new \DateTime());

        /**
         * Send notification here...
         * @var Customer $customer
         * @var ShopUser $user
         */
        $customer = $address->getCustomer();

        if (!$customer instanceof Customer) {
            return;
        }

        $user = $customer->getUser();

        if ($user instanceof ShopUser) {
            $this->sender->send('approved_address', [$user->getEmail()], ['customer' => $user->getCustomer(), 'address' => $address]);
            $notification = new Notification(null, $user, '??Felicitaciones!', 'Se ha validado tu direcci??n de env??o.', PushNotification::TYPE_ADDRESS_VALIDATED);
            $this->entityManager->persist($notification);

            /** Find children and enable those addresses. */
            foreach ($this->findChildrenAddresses($address) as $childrenAddress) {
                $this->validate($childrenAddress);
            }
        } else {
            $parentAddress = $this->findParentAddress($address);

            if ($parentAddress instanceof Address) {
                $this->validate($parentAddress);

                /** Find children and enable those addresses. */
                foreach ($this->findChildrenAddresses($parentAddress) as $childrenAddress) {
                    $this->validate($childrenAddress);
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param Address $parentAddress
     * @return Address[]
     */
    private function findChildrenAddresses(Address $parentAddress): array
    {
        return $this->addressRepository
            ->createQueryBuilder('a')
            ->andWhere('a.parent = :parent')
            ->setParameter('parent', $parentAddress)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Address $address
     * @return Address|null
     */
    private function findParentAddress(Address $address): ?Address
    {
        return $address->getParent();
    }
}
