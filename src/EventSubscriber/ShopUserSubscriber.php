<?php

namespace App\EventSubscriber;

use DateTime;
use Exception;
use App\Entity\User\ShopUser;
use Doctrine\ORM\ORMException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Sylius\Component\Mailer\Sender\SenderInterface;

/**
 * Class ShopUserSubscriber
 * @package AppBundle\EventSubscriber
 */
class ShopUserSubscriber implements EventSubscriber
{
    /**
     * @var SenderInterface
     */
    private $sender;

    /**
     * ShopUserSubscriber constructor.
     * @param SenderInterface $sender
     */
    public function __construct(
        SenderInterface $sender
    ) {
        $this->sender = $sender;
    }


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

            /** Send welcome email */
            $this->sendWelcomeEmail($entity);
        }
    }

    /**
     * @param ShopUser $user
     * @param LifecycleEventArgs $args
     * @throws ORMException
     * @throws OptimisticLockException
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
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function setTermsAndConditions(ShopUser $user, LifecycleEventArgs $args)
    {
        $user->setTermsAndConditionsAcceptedAt(new DateTime());
        $user->setVerifiedAt(new DateTime());

        $args->getEntityManager()->flush();
    }

    /**
     * Send welcome email.
     * @param ShopUser $user
     */
    private function sendWelcomeEmail(ShopUser $user)
    {
        $this->sender->send('user_registration', [$user->getCustomer()->getEmail()], ['user' => $user]);
    }
}
