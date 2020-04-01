<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use App\Repository\AboutStoreRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsService
 * @package App\Service
 * @author Rodmar Zavala <rzavala@praga.ws>
 *
 * @method string|null getFacebookUrl()
 * @method string|null getTwitterUrl()
 * @method string|null getPinterestUrl()
 * @method string|null getInstagramUrl()
 * @method string|null getAppStoreUrl()
 * @method string|null getPlayStoreUrl()
 * @method string|null getDeliveryHours()
 * @method string|null getShowProductSearchBox()
 * @method string|null getDaysToChooseInAdvanceToPurchase()
 * @method string|null getAboutUs()
 * @method string|null getPhrase()
 * @method string|null getAuthor()
 * @method string|null getEmail()
 * @method string|null getFirstPurchaseMessage()
 * @method string|null getNewAddressMessage()
 * @method string|null getMaximumPurchaseValue()
 * @method string|null getMinimumPurchaseValue()
 * @method string|null getPhoneNumber()
 */
class SettingsService
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var AboutStoreRepository $repository
     */
    private $repository;

    /**
     * DashboardService constructor.
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param AboutStoreRepository $repository
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger, AboutStoreRepository $repository)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function __call($name, $arguments)
    {
        try {
            $aboutStore = $this->repository->findLatest();

            return $aboutStore->$name();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }
}
