<?php

declare(strict_types=1);

namespace App\Factory\Product;

use App\Entity\Product\Product;
use App\Entity\User\ShopUser;
use App\Service\FavoriteService;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\ShopApiPlugin\Factory\ImageViewFactoryInterface;
use Sylius\ShopApiPlugin\Factory\Product\ProductAttributeValuesViewFactoryInterface;
use Sylius\ShopApiPlugin\Factory\Product\ProductViewFactoryInterface;
use Sylius\ShopApiPlugin\View\Product\ProductTaxonView;
use Sylius\ShopApiPlugin\View\Product\ProductView;
use Symfony\Component\Security\Core\Security;

final class ProductViewFactory implements ProductViewFactoryInterface
{
    /** @var ImageViewFactoryInterface */
    private $imageViewFactory;

    /** @var ProductAttributeValuesViewFactoryInterface */
    private $attributeValuesViewFactory;

    /** @var string */
    private $productViewClass;

    /** @var string */
    private $productTaxonViewClass;

    /** @var string */
    private $fallbackLocale;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var FavoriteService
     */
    private $favoriteService;

    public function __construct(
        ImageViewFactoryInterface $imageViewFactory,
        ProductAttributeValuesViewFactoryInterface $attributeValuesViewFactory,
        Security $security,
        FavoriteService $favoriteService,
        string $productViewClass,
        string $productTaxonViewClass,
        string $fallbackLocale
    ) {
        $this->imageViewFactory = $imageViewFactory;
        $this->attributeValuesViewFactory = $attributeValuesViewFactory;
        $this->productViewClass = $productViewClass;
        $this->productTaxonViewClass = $productTaxonViewClass;
        $this->fallbackLocale = $fallbackLocale;
        $this->security = $security;
        $this->favoriteService = $favoriteService;
    }

    /** {@inheritdoc} */
    public function create(ProductInterface $product, ChannelInterface $channel, string $locale): ProductView
    {
        /** @var ShopUser */
        $user = $this->security->getUser();

        /** @var \App\View\ShopApi\ProductView $productView */
        $productView = new $this->productViewClass();
        $productView->code = $product->getCode();
        $productView->averageRating = (string) $product->getAverageRating();

        /** @var ProductTranslationInterface $translation */
        $translation = $product->getTranslation($locale);
        $productView->name = $translation->getName();
        $productView->slug = $translation->getSlug();
        $productView->description = $translation->getDescription();
        $productView->shortDescription = $translation->getShortDescription();
        $productView->metaKeywords = $translation->getMetaKeywords();
        $productView->metaDescription = $translation->getMetaDescription();
        $productView->channelCode = $channel->getCode();
        /** @var Product $product */
        $productView->measurementUnit = $product->getMeasurementUnit();
        $productView->isFavorite = $this->favoriteService->isFavorite($product, $user);

        /** @var ProductImageInterface $image */
        foreach ($product->getImages() as $image) {
            $imageView = $this->imageViewFactory->create($image);
            $productView->images[] = $imageView;
        }

        /** @var ProductTaxonView $taxons */
        $taxons = new $this->productTaxonViewClass();
        if (null !== $product->getMainTaxon()) {
            $taxons->main = $product->getMainTaxon()->getCode();
        }

        /** @var TaxonInterface $taxon */
        foreach ($product->getTaxons() as $taxon) {
            $taxons->others[] = $taxon->getCode();
        }

        $productView->taxons = $taxons;

        $productView->attributes = $this->attributeValuesViewFactory->create(
            $product->getAttributesByLocale($locale, $this->fallbackLocale)->toArray(),
            $locale
        );

        return $productView;
    }
}
