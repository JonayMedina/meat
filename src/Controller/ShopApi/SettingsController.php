<?php

namespace App\Controller\ShopApi;

use App\Repository\TermsAndConditionsRepository;
use Symfony\Component\Intl\Currencies;
use App\Repository\AboutStoreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @var TermsAndConditionsRepository
     */
    private $termsAndConditionsRepository;

    /**
     * SettingsController constructor.
     * @param AboutStoreRepository $repository
     * @param TermsAndConditionsRepository $termsAndConditionsRepository
     */
    public function __construct(AboutStoreRepository $repository, TermsAndConditionsRepository $termsAndConditionsRepository)
    {
        $this->repository = $repository;
        $this->termsAndConditionsRepository = $termsAndConditionsRepository;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_settings",
     *     methods={"GET"}
     * )
     *
     * @param CurrencyContextInterface $currencyContext
     * @param UrlGeneratorInterface $urlGenerator
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction(CurrencyContextInterface $currencyContext, UrlGeneratorInterface $urlGenerator)
    {
        $statusCode = Response::HTTP_OK;
        $aboutStore = $this->repository->findLatest();

        $aboutStore->currencyCode = $currencyContext->getCurrencyCode();
        $aboutStore->currencySymbol = Currencies::getSymbol($aboutStore->currencyCode);
        $aboutStore->termsAndConditionsUrl = $urlGenerator->generate('store_terms_page', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $view = $this->view($aboutStore, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/terms-and-conditions.{_format}",
     *     name="shop_api_terms_and_conditions",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function termsAndConditionsAction()
    {
        $statusCode = Response::HTTP_OK;
        $aboutStore = $this->termsAndConditionsRepository->findLatest();

        $view = $this->view([
            'text' => $aboutStore->getText(),
            'updated_at' => $aboutStore->getUpdatedAt()->format('c')
        ], $statusCode);

        return $this->handleView($view);
    }
}
