<?php

namespace App\Controller\AdminApi;

use App\Model\APIResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * DashboardController
 */
class DashboardController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * DashboardController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route(
     *     ".{_format}",
     *     name="api_index",
     *     methods={"GET"}
     * )
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction()
    {
        $aboutStore = $this->entityManager->getRepository('App:AboutStore')->findLatest();

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Ok', [
            'categories_updated_at' => ($aboutStore && $aboutStore->getCategoriesUpdatedAt()) ? $aboutStore->getCategoriesUpdatedAt()->format('c') : null,
            'products_updated_at' => ($aboutStore && $aboutStore->getProductsUpdatedAt()) ? $aboutStore->getProductsUpdatedAt()->format('c') : null,
            'coupons_updated_at' => ($aboutStore && $aboutStore->getCouponsUpdatedAt()) ? $aboutStore->getCouponsUpdatedAt()->format('c') : null,
        ]);

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

}
