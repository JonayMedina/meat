<?php

namespace App\EventSubscriber;

use DateTime;
use Exception;
use App\Entity\User\ShopUser;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class CompanySubscriber
 * @package AppBundle\EventSubscriber
 */
class ShopUserSubscriber implements EventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postPersist'];
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ShopUser) {
            /** Accept terms and conditions. */
            $this->setTermsAndConditions($entity, $args);

            /** Add email to ShopUser if not set && is valid email. */
            $this->updateShopUserEmail($entity, $args);
        }
    }

    /**
     * @param ShopUser $user
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function updateShopUserEmail(ShopUser $user, LifecycleEventArgs $args)
    {
        $email = $user->getUsername();

        if (!$user->getEmail()) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $user->setEmail($email);
                $args->getEntityManager()->flush();
            }
        }
    }

    /**
     * @param ShopUser $user
     * @param LifecycleEventArgs $args
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function setTermsAndConditions(ShopUser $user, LifecycleEventArgs $args)
    {
        $user->setTermsAndConditionsAcceptedAt(new DateTime());
        $user->setVerifiedAt(new DateTime());

        $args->getEntityManager()->flush();
    }
}
