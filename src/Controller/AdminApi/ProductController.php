<?php

namespace App\Controller\AdminApi;

use App\Entity\Channel\Channel;
use App\Entity\Locale\Locale;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Entity\Taxonomy\Taxon;
use App\Pagination\PaginationFactory;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Liip\ImagineBundle\Service\FilterService;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductVariantRepository;

/**
 * ProductController
 * @Route("/products")
 */
class ProductController extends AbstractFOSRestController
{
    const ITEMS_PER_PAGE = 10;

    const ORIGINAL_IMAGE_KEY = 'shop_api_product_original';

    /** @var ProductRepository $productRepository */
    private $productRepository;

    /** @var ProductVariantRepository $productVariantRepository */
    private $productVariantRepository;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var ChannelContextInterface $channelContext */
    private $channelContext;

    /**
     * @var FilterService
     */
    private $filterService;

    /**
     * ProductController constructor.
     * @param ProductRepository $productRepository
     * @param ProductVariantRepository $productVariantRepository
     * @param EntityManagerInterface $entityManager
     * @param ChannelContextInterface $channelContext
     * @param FilterService $filterService
     */
    public function __construct(
        ProductRepository $productRepository,
        ProductVariantRepository $productVariantRepository,
        EntityManagerInterface $entityManager,
        ChannelContextInterface $channelContext,
        FilterService $filterService

    ) {
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->entityManager = $entityManager;
        $this->channelContext = $channelContext;
        $this->filterService = $filterService;
    }

    /**
     * @Route(
     *     "/.{_format}",
     *     name="admin_api_product_index",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @param PaginationFactory $paginationFactory
     * @return Response
     */
    public function indexAction(Request $request, PaginationFactory $paginationFactory): Response
    {
        $statusCode = Response::HTTP_OK;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', self::ITEMS_PER_PAGE);
        $search = $request->query->get('search');

        $queryBuilder = $this->getQueryBuilder($search);
        $paginatedCollection = $paginationFactory->createCollection($queryBuilder, $search, $page, $limit, 'admin_api_product_index', [], 'Product list.', $statusCode, 'info');

        $list = [];
        foreach ($paginatedCollection->recordset as $product) {
            $list[] = $this->serializeProduct($product);
        }
        $paginatedCollection->recordset = $list;

        $view = $this->view($paginatedCollection, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/.{_format}",
     *     name="admin_api_product_new",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request): Response
    {

    }

    /**
     * @Route(
     *     "/{code}.{_format}",
     *     name="admin_api_product_edit",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request): Response
    {

    }

    /**
     * @Route(
     *     "/{code}.{_format}",
     *     name="admin_api_product_delete",
     *     methods={"DELETE"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request): Response
    {
        $product = $this->getProduct($request);
        $this->entityManager->remove($product);

        try {
            $this->entityManager->flush();
            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $exception->getMessage(), 'code' => $statusCode], $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @param $search
     * @return QueryBuilder
     */
    private function getQueryBuilder($search): QueryBuilder
    {
        $queryBuilder = $this->productRepository
            ->createQueryBuilder('product')
            ->leftJoin('product.translations', 'translations')
            ->andWhere('translations.locale = :locale')
            ->setParameter('locale', Locale::DEFAULT_LOCALE)
            ->orderBy('product.createdAt', 'DESC');

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('product.code LIKE :search OR translations.name LIKE :search OR translations.description LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $queryBuilder;
    }

    /**
     * @param Request $request
     * @return Product
     */
    private function getProduct(Request $request): Product
    {
        $code = $request->get('code');
        /** @var Product $product */
        $product = $this->productRepository->findOneBy(['code' => $code]);

        if (!$product instanceof Product) {
            throw new NotFoundHttpException('Product not found.');
        }

        return $product;
    }

    /**
     * @param Product $product
     * @return array
     */
    private function serializeProduct(Product $product): array
    {
        $photoURL = null;
        $variant = null;
        $category = [];
        $categories = [];

        if (count($product->getVariants())) {
            /** @var ProductVariant $variant */
            $variant = $product->getVariants()[0];
        }

        if ($product->getImages()[0] ?? null) {
            $photoURL = $this->filterService->getUrlOfFilteredImage($product->getImages()[0]->getPath(), self::ORIGINAL_IMAGE_KEY);
        }

        if ($product->getMainTaxon() instanceof Taxon) {
            $category = $this->serializeTaxon($product->getMainTaxon());
        }

        foreach ($product->getTaxons() as $taxon) {
            $categories[] = $this->serializeTaxon($taxon);
        }

        /** @var Channel $channel */
        $channel = $this->channelContext->getChannel();

        return [
            'id' => $product->getId(),
            'category' => $category,
            'categories' => $categories,
            'code' => $product->getCode(),
            'on_stock' => ($variant->getOnHand() > 0),
            'enabled' => $product->isEnabled(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $variant->getChannelPricingForChannel($channel)->getOriginalPrice(),
            'offerPrice' => $variant->getChannelPricingForChannel($channel)->getPrice(),
            'keywords' => $product->getMetaKeywords(),
            'photo' => $photoURL,
        ];
    }

    /**
     * @param TaxonInterface|null $taxon
     * @return array
     */
    private function serializeTaxon(?TaxonInterface $taxon): array
    {
        $photoURL = null;

        if ($taxon->getImages()[0] ?? null) {
            $photoURL = $this->filterService->getUrlOfFilteredImage($taxon->getImages()[0]->getPath(), self::ORIGINAL_IMAGE_KEY);
        }

        return [
            'id' => $taxon->getId(),
            'name' => $taxon->getName(),
            'code' => $taxon->getCode(),
            'photo' => $photoURL,
        ];
    }
}
