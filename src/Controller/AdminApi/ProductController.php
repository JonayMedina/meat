<?php

namespace App\Controller\AdminApi;

use App\Entity\Channel\Channel;
use App\Entity\Channel\ChannelPricing;
use App\Entity\Locale\Locale;
use App\Entity\Product\Product;
use App\Entity\Product\ProductAssociation;
use App\Entity\Product\ProductImage;
use App\Entity\Product\ProductTaxon;
use App\Entity\Product\ProductVariant;
use App\Entity\Taxonomy\Taxon;
use App\Form\AdminApi\ProductAssociationType;
use App\Form\AdminApi\ProductType;
use App\Pagination\PaginationFactory;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Hshn\Base64EncodedFile\HttpFoundation\File\Base64EncodedFile;
use Hshn\Base64EncodedFile\HttpFoundation\File\UploadedBase64EncodedFile;
use Liip\ImagineBundle\Service\FilterService;
use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductAssociationTypeRepository;
use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Intl\Currencies;
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

    const IN_STOCK_VALUE = 1000000;

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
     * @var CurrencyContextInterface
     */
    private $currencyContext;

    /**
     * @var ImageUploaderInterface
     */
    private $imageUploader;

    /**
     * @var TaxonRepository
     */
    private $categoryRepository;

    /**
     * @var ProductAssociationTypeRepository
     */
    private $associationTypeRepository;

    /**
     * ProductController constructor.
     * @param ProductRepository $productRepository
     * @param ProductVariantRepository $productVariantRepository
     * @param EntityManagerInterface $entityManager
     * @param ChannelContextInterface $channelContext
     * @param FilterService $filterService
     * @param CurrencyContextInterface $currencyContext
     * @param ImageUploaderInterface $imageUploader
     * @param TaxonRepository $taxonRepository
     * @param ProductAssociationTypeRepository $associationTypeRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        ProductVariantRepository $productVariantRepository,
        EntityManagerInterface $entityManager,
        ChannelContextInterface $channelContext,
        FilterService $filterService,
        CurrencyContextInterface $currencyContext,
        ImageUploaderInterface $imageUploader,
        TaxonRepository $taxonRepository,
        ProductAssociationTypeRepository $associationTypeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->entityManager = $entityManager;
        $this->channelContext = $channelContext;
        $this->filterService = $filterService;
        $this->currencyContext = $currencyContext;
        $this->imageUploader = $imageUploader;
        $this->categoryRepository = $taxonRepository;
        $this->associationTypeRepository = $associationTypeRepository;
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
     *     "/{code}.{_format}",
     *     name="admin_api_product_show",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request): Response
    {
        $product = $this->getProduct($request);
        $statusCode = Response::HTTP_OK;
        $view = $this->view($this->serializeProduct($product), $statusCode);

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
        $form = $this->createForm(ProductType::class, null, ['validation_groups' => ['creation']]);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->get('category')->getData();
            $categories = $form->get('categories')->getData();
            $code = $form->get('code')->getData();
            $name = $form->get('name')->getData();
            $description = $form->get('description')->getData();
            $price = $form->get('price')->getData();
            $offerPrice = $form->get('offerPrice')->getData();
            $measurementUnit = $form->get('measurementUnit')->getData();
            $keywords = $form->get('keywords')->getData();
            $photo = $form->get('photo')->getData();

            $product = $this->createProduct($code);
            $product = $this->updateProduct($product, $name, $description, $category, $categories, $price, $offerPrice, $measurementUnit, $keywords, $photo);

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->entityManager->clear();

            /** @var Product $reloadedProduct */
            $reloadedProduct = $this->productRepository->find($product->getId());

            $view = $this->view($this->serializeProduct($reloadedProduct), Response::HTTP_CREATED);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_BAD_REQUEST;
        $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $form->getErrors(), 'code' => $statusCode], $statusCode);

        return $this->handleView($view);
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
        $product = $this->getProduct($request);

        $form = $this->createForm(ProductType::class, null, []);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->get('category')->getData();
            $categories = $form->get('categories')->getData();
            $name = $form->get('name')->getData();
            $description = $form->get('description')->getData();
            $price = $form->get('price')->getData();
            $offerPrice = $form->get('offerPrice')->getData();
            $measurementUnit = $form->get('measurementUnit')->getData();
            $keywords = $form->get('keywords')->getData();
            $photo = $form->get('photo')->getData();

            $product = $this->updateProduct($product, $name, $description, $category, $categories, $price, $offerPrice, $measurementUnit, $keywords, $photo);
            $this->entityManager->flush();

            $view = $this->view($this->serializeProduct($product), Response::HTTP_OK);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_BAD_REQUEST;
        $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $form->getErrors(), 'code' => $statusCode], $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{code}/associations.{_format}",
     *     name="admin_api_product_associate",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function associateAction(Request $request): Response
    {
        $form = $this->createForm(ProductAssociationType::class);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $this->getProduct($request);
            $similarProducts = $form->get('similar_products')->getData();
            $complimentaryProducts = $form->get('complimentary_products')->getData();

            if (count($similarProducts) > 0) {
                $associationType = $this->getAssociationType('similar_products', 'Productos similares');
                $this->removeProductAssociations($product, $associationType);

                foreach ($similarProducts as $associatedProduct) {
                    $this->associateProduct($product, $associationType, $associatedProduct);
                }
            }

            if (count($complimentaryProducts) > 0) {
                $associationType = $this->getAssociationType('complimentary_products', 'Productos complementarios');
                $this->removeProductAssociations($product, $associationType);

                foreach ($complimentaryProducts as $associatedProduct) {
                    $this->associateProduct($product, $associationType, $associatedProduct);
                }
            }

            $this->entityManager->flush();
            $statusCode = Response::HTTP_CREATED;
            $view = $this->view($this->serializeProduct($product), $statusCode);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_BAD_REQUEST;
        $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $form->getErrors(), 'code' => $statusCode], $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{code}/out-of-stock.{_format}",
     *     name="admin_api_product_out_of_stock",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function outOfStockAction(Request $request): Response
    {
        $product = $this->getProduct($request);
        $statusCode = Response::HTTP_OK;

        /** @var ProductVariant $variant */
        $variant = $product->getVariants()[0];
        $variant->setOnHand(0);
        $this->entityManager->flush();

        $view = $this->view($this->serializeProduct($product), $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{code}/in-stock.{_format}",
     *     name="admin_api_product_in_stock",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function inStockAction(Request $request): Response
    {
        $product = $this->getProduct($request);
        $statusCode = Response::HTTP_OK;

        /** @var ProductVariant $variant */
        $variant = $product->getVariants()[0];
        $variant->setOnHand(self::IN_STOCK_VALUE);
        $this->entityManager->flush();

        $view = $this->view($this->serializeProduct($product), $statusCode);

        return $this->handleView($view);
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
            'code' => $product->getCode(),
            'category' => $category,
            'categories' => $categories,
            'inStock' => ($variant->getOnHand() > 0),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'currency' => [
                'code' => $this->currencyContext->getCurrencyCode(),
                'symbol' => Currencies::getSymbol($this->currencyContext->getCurrencyCode())
            ],
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

    /**
     * @param $code
     * @return Product|null
     */
    private function createProduct($code): ?Product
    {
        $product = $this->productRepository->findOneBy(['code' => $code]);

        if ($product instanceof Product) {
            throw new BadRequestHttpException('Product already exists.');
        }

        $product = new Product();
        $product->setCode($code);

        $variant = new ProductVariant();
        $variant->setProduct($product);
        $variant->setCode($code);

        $product->addVariant($variant);

        $this->entityManager->persist($product);
        $this->entityManager->persist($variant);

        return $product;
    }

    /**
     * @param Product $product
     * @param $name
     * @param $description
     * @param $category
     * @param $categories
     * @param $price
     * @param $offerPrice
     * @param $measurementUnit
     * @param $keywords
     * @param $base64Content
     * @return Product|null
     */
    private function updateProduct(Product $product, $name, $description, $category, $categories, $price, $offerPrice, $measurementUnit, $keywords, $base64Content): ?Product
    {
        /** @var ProductVariant $variant */
        $variant = $product->getVariants()[0];
        /** @var Channel $channel */
        $channel = $this->channelContext->getChannel();

        $product->setCurrentLocale(Locale::DEFAULT_LOCALE);
        $product->setSlug($product->getCode());

        if (isset($name)) {
            $product->setName($name);
        }

        if (isset($description)) {
            $product->setDescription($description);
            $product->setMetaDescription($description);
        }

        if (isset($keywords)) {
            $product->setMetaKeywords($keywords);
        }

        $variant->setCurrentLocale(Locale::DEFAULT_LOCALE);

        if (isset($name)) {
            $variant->setName($name);
        }

        if (isset($measurementUnit)) {
            $variant->setMeasurementUnit($measurementUnit);
        }

        $channelPricing = $variant->getChannelPricingForChannel($channel) ?? new ChannelPricing();

        if (isset($price)) {
            $channelPricing->setOriginalPrice($price);
        }

        if (isset($offerPrice)) {
            $channelPricing->setPrice($offerPrice);
        }

        $channelPricing->setProductVariant($variant);
        $channelPricing->setChannelCode($channel->getCode());

        $this->entityManager->persist($channelPricing);

        $product->addVariant($variant);
        $variant->setProduct($product);

        if ($category) {
            /** Main Category */
            $product->setMainTaxon($category);
        }

        if (count($categories) > 0) {
            foreach ($product->getProductTaxons() as $productTaxon) {
                $this->entityManager->remove($productTaxon);
            }

            foreach ($categories as $taxon) {
                $productTaxon = new ProductTaxon();
                $productTaxon->setTaxon($taxon);
                $productTaxon->setProduct($product);

                $this->entityManager->persist($productTaxon);
                $product->addProductTaxon($productTaxon);
            }
        }

        /** Photo */
        if (!empty($base64Content)) {
            foreach ($product->getImages() as $image) {
                $this->entityManager->remove($image);
            }

            /** @var ImageInterface $productImage */
            $productImage = new ProductImage();
            $productImage->setFile(new UploadedBase64EncodedFile(new Base64EncodedFile($base64Content)));

            $this->entityManager->persist($productImage);
            $this->imageUploader->upload($productImage);

            $product->addImage($productImage);
        }

        return $product;
    }

    private function getAssociationType(string $code, string $name): \App\Entity\Product\ProductAssociationType
    {
        $association = $this->associationTypeRepository->findOneBy(['code' => $code]);

        if (!$association instanceof \App\Entity\Product\ProductAssociationType) {
            $association = new \App\Entity\Product\ProductAssociationType();
            $association->setCurrentLocale(Locale::DEFAULT_LOCALE);
            $association->setCode($code);
            $association->setName($name);

            $this->entityManager->persist($association);
        }

        return $association;
    }

    /**
     * @param Product $product
     * @param \App\Entity\Product\ProductAssociationType $associationType
     */
    private function removeProductAssociations(Product $product, \App\Entity\Product\ProductAssociationType $associationType): void
    {
        foreach ($product->getAssociations() as $association) {
            if ($association->getType()->getCode() == $associationType->getCode()) {
                $this->entityManager->remove($association);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param Product $product
     * @param \App\Entity\Product\ProductAssociationType $associationType
     * @param Product $associatedProduct
     * @return ProductAssociation
     */
    private function associateProduct(Product $product, \App\Entity\Product\ProductAssociationType $associationType, Product $associatedProduct): ProductAssociation
    {
        $association = $this->entityManager->getRepository('App:Product\ProductAssociation')
            ->findOneBy(['owner' => $product, 'type' => $associationType]);

        if (!$association instanceof ProductAssociation) {
            $association = new ProductAssociation();
            $association->setOwner($product);
            $association->setType($associationType);

            $this->entityManager->persist($association);
        }

        $association->addAssociatedProduct($associatedProduct);
        $this->entityManager->flush();

        return $association;
    }
}
