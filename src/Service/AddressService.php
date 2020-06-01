<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User\ShopUser;
use App\Entity\PushNotification;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
     * AddressService constructor.
     * @param AddressRepositoryInterface $addressRepository
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->addressRepository = $addressRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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

        if ($customer instanceof Customer) {
            $user = $customer->getUser();

            if ($user instanceof ShopUser) {
                $notification = new Notification(null, $user, '¡Felicitaciones!', 'Se ha validado tu dirección de envío.', PushNotification::TYPE_ADDRESS_VALIDATED);
                $this->entityManager->persist($notification);
                $this->entityManager->flush();
            }
        } else {
            $parentAddress = $this->findParentAddress($address);
            $this->validate($parentAddress);

            /** Find children and enable those addresses. */
            foreach ($this->findChildrenAddresses($parentAddress) as $childrenAddress) {
                $this->validate($childrenAddress);
            }
        }
    }

    /**
     * @param Address $parentAddress
     * @return Address[]
     */
    private function findChildrenAddresses(Address $parentAddress): array
    {
        return $this->addressRepository
            ->createQueryBuilder('a')
            ->andWhere('a.annotations = :annotations')
            ->andWhere('a.type = :type')
            ->andWhere('a.fullAddress = :fullAddress')
            ->andWhere('a.customer IS NULL')
            ->andWhere('a.status = :status')
            ->setParameter('annotations', $parentAddress->getAnnotations())
            ->setParameter('type', $parentAddress->getType())
            ->setParameter('fullAddress', $parentAddress->getFullAddress())
            ->setParameter('status', Address::STATUS_PENDING)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Address $address
     * @return Address|null
     */
    private function findParentAddress(Address $address): ?Address
    {
        try {
            return $this->addressRepository
                ->createQueryBuilder('a')
                ->andWhere('a.annotations = :annotations')
                ->andWhere('a.type = :type')
                ->andWhere('a.fullAddress = :fullAddress')
                ->andWhere('a.customer IS NOT NULL')
                ->setParameter('annotations', $address->getAnnotations())
                ->setParameter('type', $address->getType())
                ->setParameter('fullAddress', $address->getFullAddress())
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }

}
