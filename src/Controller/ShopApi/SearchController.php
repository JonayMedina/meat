<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use App\Entity\Product\Product;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Channel\Context\ChannelContextInterface;

/**
 * SearchController
 * @Route("/search")
 */
class SearchController extends AbstractFOSRestController
{
    /**
     * @var FilterService
     */
    private $filterService;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * SearchController constructor.
     * @param FilterService $filterService
     * @param ChannelContextInterface $channelContext
     * @param EntityManagerInterface $entityManager
     * @param ProductService $productService
     */
    public function __construct(FilterService $filterService, ChannelContextInterface $channelContext, EntityManagerInterface $entityManager, ProductService $productService)
    {
        $this->filterService = $filterService;
        $this->channelContext = $channelContext;
        $this->entityManager = $entityManager;
        $this->productService = $productService;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_search",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function searchAction(Request $request)
    {
        $term = $request->get('term');
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $limit;

        $locale = $request->getLocale();

        $list = [];
        $statusCode = Response::HTTP_OK;

        /** @var Product[] $products */
        $products = $this->entityManager->getRepository('App:Product\Product')
            ->searchQuery($term, $locale, $limit, $offset)
            ->getQuery()
            ->getResult();

        foreach ($products as $product) {
            $list[] = $this->productService->serialize($product);
        }

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Search result.', $list);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
