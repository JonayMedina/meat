<?php

namespace App\Controller\ShopApi;

use Symfony\Component\Intl\Currencies;
use App\Repository\AboutStoreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Currency\Context\CurrencyContextInterface;

/**
 * SettingsController
 * @Route("/settings")
 */
class SettingsController extends AbstractFOSRestController
{
    /**
     * @var AboutStoreRepository
     */
    private $repository;

    /**
     * DashboardController constructor.
     * @param AboutStoreRepository $repository
     */
    public function __construct(AboutStoreRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_settings",
     *     methods={"GET"}
     * )
     *
     * @param CurrencyContextInterface $currencyContext
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction(CurrencyContextInterface $currencyContext)
    {
        $statusCode = Response::HTTP_OK;
        $aboutStore = $this->repository->findLatest();

        $aboutStore->currencyCode = $currencyContext->getCurrencyCode();
        $aboutStore->currencySymbol = Currencies::getSymbol($aboutStore->currencyCode);

        $view = $this->view($aboutStore, $statusCode);

        return $this->handleView($view);
    }
}
