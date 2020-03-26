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
 * @method string getFacebookUrl()
 * @method string getTwitterUrl()
 * @method string getPinterestUrl()
 * @method string getInstagramUrl()
 * @method string getAppStoreUrl()
 * @method string getPlayStoreUrl()
 * @method string getDeliveryHours()
 * @method string getShowProductSearchBox()
 * @method string getDaysToChooseInAdvanceToPurchase()
 * @method string getAboutUs()
 * @method string getPhrase()
 * @method string getAuthor()
 * @method string getFirstPurchaseMessage()
 * @method string getNewAddressMessage()
 * @method string getMaximumPurchaseValue()
 * @method string getMinimumPurchaseValue()
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
