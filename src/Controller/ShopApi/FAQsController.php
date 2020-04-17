<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use App\Repository\FAQRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * FAQsController
 * @Route("/faqs")
 */
class FAQsController extends AbstractFOSRestController
{
    /**
     * @var FAQRepository
     */
    private $repository;

    /**
     * DashboardController constructor.
     * @param FAQRepository $repository
     */
    public function __construct(FAQRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_faqs",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $statusCode = Response::HTTP_OK;
        $faqs = $this->repository->createQueryBuilder('faq')
            ->orderBy('faq.position', 'ASC')
            ->getQuery()
            ->getResult();

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'FAQs list', $faqs);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
