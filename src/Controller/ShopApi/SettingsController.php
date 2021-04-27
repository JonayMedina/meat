<?php

namespace App\Controller\ShopApi;

use Symfony\Component\Intl\Currencies;
use App\Entity\Shipping\ShippingMethod;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AboutStoreRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TermsAndConditionsRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * @var TermsAndConditionsRepository
     */
    private $termsAndConditionsRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * SettingsController constructor.
     * @param AboutStoreRepository $repository
     * @param TermsAndConditionsRepository $termsAndConditionsRepository
     * @param EntityManagerInterface $entityManager
     * @param ChannelContextInterface $channelContext
     */
    public function __construct(
        AboutStoreRepository $repository,
        TermsAndConditionsRepository $termsAndConditionsRepository,
        EntityManagerInterface $entityManager,
        ChannelContextInterface $channelContext
    ) {
        $this->repository = $repository;
        $this->termsAndConditionsRepository = $termsAndConditionsRepository;
        $this->entityManager = $entityManager;
        $this->channelContext = $channelContext;
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
        $aboutStore->shippingCost = $this->getShippingCost();

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

    /**
     * Returns the current shipping cost.
     * @return float
     */
    private function getShippingCost()
    {
        $shippingMethod = $this->entityManager->getRepository('App:Shipping\ShippingMethod')
            ->findOneBy(['code' => ShippingMethod::DEFAULT_SHIPPING_METHOD]);

        return $shippingMethod->getConfiguration()[$this->channelContext->getChannel()->getCode()]['amount'];
    }
}
