<?php

namespace App\Controller\ShopApi;

use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Taxonomy\Taxon;
use App\Entity\Product\Product;
use App\Entity\Channel\Channel;
use App\Service\FavoriteService;
use Doctrine\ORM\NoResultException;
use App\Repository\ProductRepository;
use App\Entity\Product\ProductVariant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Core\Model\ImageInterface;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * TaxonProductsController
 * @Route("/taxon-products")
 */
class TaxonProductsController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FavoriteService
     */
    private $favoriteService;

    /**
     * @var Security
     */
    private $security;

    /** @var ChannelContextInterface $channelContext */
    private $channelContext;

    /**
     * @var CurrencyContextInterface
     */
    private $currencyContext;

    /**
     * SwitchEmailController constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     * @param FavoriteService $favoriteService
     * @param Security $security
     * @param ChannelContextInterface $channelContext
     * @param CurrencyContextInterface $currencyContext
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        FavoriteService $favoriteService,
        Security $security,
        ChannelContextInterface $channelContext,
        CurrencyContextInterface $currencyContext
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->translator = $translator;
        $this->favoriteService = $favoriteService;
        $this->security = $security;
        $this->channelContext = $channelContext;
        $this->currencyContext = $currencyContext;
    }

    /**
     * @Route(
     *     "/by-code/{taxonCode}",
     *     name="sylius_shop_api_product_show_catalog_by_code",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function byCodeAction(Request $request): Response
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $taxonCode = $request->get('taxonCode');
        $taxon = $this->entityManager->getRepository('App:Taxonomy\Taxon')->findOneBy(['code' => $taxonCode]);

        if ($limit < 1) {
            throw new NotFoundHttpException('Limit must be greater or equal than 1');
        }

        if (!$taxon instanceof Taxon) {
            throw new NotFoundHttpException('Category not found');
        }

        /** @var Channel $channel */
        $channel = $this->channelContext->getChannel();

        $total = $this->getItemsTotal($taxon);
        $pages = ceil($total / $limit);

        $response = [
            'page' => (int)$page,
            'limit' => (int)$limit,
            'pages' => (int)$pages,
            'total' => (int)$total,
            'items' => $this->getItems($taxon, $channel, $page, $limit),
        ];

        return new JsonResponse($response, Response::HTTP_OK);
    }

    /**
     * @param Taxon $taxon
     * @return int
     */
    private function getItemsTotal(Taxon $taxon): int
    {
        try {
            return $this->getItemsQuery($taxon)
                ->select('COUNT(product)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $exception) {
            return 0;
        } catch (NonUniqueResultException $exception) {
            return 0;
        }
    }

    /**
     * @param Taxon $taxon
     * @param Channel $channel
     * @param int $page
     * @param int $limit
     * @return array
     */
    private function getItems(Taxon $taxon, Channel $channel, $page = 1, $limit = 10000): array
    {
        $data = [];
        $offset = ($page - 1) * $limit;

        /** @var ShopUser $user */
        $user = $this->security->getUser();

        /** @var Product[] $items */
        $items = $this->getItemsQuery($taxon)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('product.code')
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            $data[] = $this->serializeItem($item, $user, $channel);
        }

        return $data;
    }

    /**
     * @param Taxon $taxon
     * @return QueryBuilder
     */
    private function getItemsQuery(Taxon $taxon): QueryBuilder
    {
        /** @var ProductRepository */
        $productRepository = $this->entityManager->getRepository('App:Product\Product');

        return $productRepository
            ->createQueryBuilder('product')
            ->leftJoin('product.variants', 'variant')
            ->leftJoin('product.productTaxons', 'productTaxons')
            ->andWhere('productTaxons.taxon = :taxon')
            ->setParameter('taxon', $taxon);
    }

    /**
     * @param Product $product
     * @param ?ShopUser $user
     * @param Channel|null $channel
     * @return array
     */
    private function serializeItem(Product $product, ?ShopUser $user, ?Channel $channel): array
    {
        $variant = $product->getVariants()[0];
        $image = $product->getImages()[0];
        $variants = $this->serializeVariant($product->getVariants(), $channel);

        return [
            'code' => $product->getCode(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'description' => $variant->getDescriptor(),
            'on_hand' => ($variant->getOnHand() > 0),
            'variants' => $variants,
            'thumbnail' => $this->getImageRoute($image),
            'measurement_unit' => $product->getMeasurementUnit(),
            'is_favorite' => $this->favoriteService->isFavorite($product, $user),
        ];
    }

    /**
     * @param ImageInterface $image
     * @return string
     */
    private function getImageRoute(ImageInterface $image): string
    {
        $filter = 'mobile_standard_thumbnail';

        return "https://meathouse-assets.s3.amazonaws.com/media/cache/" . $filter . "/" . $image->getPath();
    }

    /**
     * @param $variants
     * @param Channel $channel
     * @return array
     */
    private function serializeVariant($variants, Channel $channel)
    {
        $data = [];

        /** @var ProductVariant[] $variants */
        foreach ($variants as $variant) {
            $data[] = [
                'code' => $variant->getCode(),
                'name' => $variant->getName(),
                'price' => [
                    'current' => $variant->getChannelPricingForChannel($channel) ? $variant->getChannelPricingForChannel($channel)->getPrice() : null,
                    'currency' => $this->currencyContext->getCurrencyCode(),
                ],
                'original_price' => [
                    'current' => $variant->getChannelPricingForChannel($channel) ? $variant->getChannelPricingForChannel($channel)->getOriginalPrice() : null,
                    'currency' => $this->currencyContext->getCurrencyCode(),
                ],
            ];
        }

        return $data;
    }
}
