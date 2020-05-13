<?php

namespace App\Controller\ShopApi;

use App\Entity\Favorite;
use App\Model\APIResponse;
use App\Entity\User\ShopUser;
use App\Entity\Product\Product;
use App\Service\ProductService;
use App\Service\FavoriteService;
use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * FavoriteController
 * @Route("/favorites")
 */
class FavoriteController extends AbstractFOSRestController
{
    /**
     * @var FavoriteService
     */
    private $favoriteService;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var FavoriteRepository
     */
    private $repository;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * FavoriteController constructor.
     * @param FavoriteRepository $repository
     * @param FavoriteService $favoriteService
     * @param ProductRepository $productRepository
     * @param ProductService $productService
     */
    public function __construct(FavoriteRepository $repository, FavoriteService $favoriteService, ProductRepository $productRepository, ProductService $productService)
    {
        $this->repository = $repository;
        $this->favoriteService = $favoriteService;
        $this->productRepository = $productRepository;
        $this->productService = $productService;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_favorites",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $statusCode = Response::HTTP_OK;
        /** @var Favorite[] $favorites */
        $favorites = $this->repository->createQueryBuilder('favorite')
            ->orderBy('favorite.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        foreach ($favorites as $favorite) {
            $product = $this->productService->serialize($favorite->getProduct());

            $favorite->virtualProduct = $product;
        }

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Favorite list', $favorites);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * Returns 200 HTTP Code if product exists as favorite, 404, if not.
     * @Route(
     *     "/{code}.{_format}",
     *     name="shop_api_is_in_favorites",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        /** @var Product $product */
        $product = $this->productRepository->findOneBy(['code' => $request->get('code')]);
        /** @var ShopUser $user */
        $user = $this->getUser();

        if (!$product instanceof Product) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Product not found.', []);

            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        if ($this->favoriteService->isFavorite($product, $user)) {
            $statusCode = Response::HTTP_OK;
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Is Favorite', []);

            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_NOT_FOUND;
        $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Is not favorite', []);

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * Add product code to favorites
     * @Route(
     *     "/{code}.{_format}",
     *     name="shop_api_add_to_favorites",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $statusCode = Response::HTTP_NOT_FOUND;
        $response = [
            'code' => $statusCode,
            'type' => 'error',
            'message' => 'Not found.'
        ];

        /** @var Product $product */
        $product = $this->productRepository->findOneBy(['code' => $request->get('code')]);
        /** @var ShopUser $user */
        $user = $this->getUser();

        if (!$product instanceof Product) {
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        if ($this->favoriteService->addFavorite($product, $user)) {
            $statusCode = Response::HTTP_CREATED;
            $response = [
                'code' => $statusCode,
                'type' => 'info',
                'message' => 'Saved'
            ];
        } else {
            $statusCode = Response::HTTP_OK;
            $response = [
                'code' => $statusCode,
                'type' => 'warning',
                'message' => 'Already in favorites.'
            ];
        }

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * Remove product code from favorites
     * @Route(
     *     "/{code}.{_format}",
     *     name="shop_api_remove_from_favorites",
     *     methods={"DELETE"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $statusCode = Response::HTTP_NOT_FOUND;
        $response = [
            'code' => $statusCode,
            'type' => 'error',
            'message' => 'Not found.'
        ];

        /** @var Product $product */
        $product = $this->productRepository->findOneBy(['code' => $request->get('code')]);
        /** @var ShopUser $user */
        $user = $this->getUser();

        if (!$product instanceof Product) {
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        if ($this->favoriteService->removeFavorite($product, $user)) {
            $statusCode = Response::HTTP_NO_CONTENT;
            $response = [];
        }

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
