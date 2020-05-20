<?php

namespace App\Controller\ShopApi;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TermsAndConditionsRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * TermsAndConditionsController
 * @Route("/terms-and-conditions")
 */
class TermsAndConditionsController extends AbstractFOSRestController
{
    /**
     * @var TermsAndConditionsRepository
     */
    private $repository;

    /**
     * TermsAndConditionsController constructor.
     * @param TermsAndConditionsRepository $termsAndConditionsRepository
     */
    public function __construct(TermsAndConditionsRepository $termsAndConditionsRepository)
    {
        $this->repository = $termsAndConditionsRepository;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_terms_and_conditions",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $statusCode = Response::HTTP_OK;
        $aboutStore = $this->repository->findLatest();

        $view = $this->view([
            'text' => $aboutStore->getText(),
            'updated_at' => $aboutStore->getUpdatedAt()->format('c')
        ], $statusCode);

        return $this->handleView($view);
    }
}
