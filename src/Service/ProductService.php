<?php

namespace App\Service;

use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use Liip\ImagineBundle\Service\FilterService;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;

class ProductService
{
    /** @var ChannelContextInterface $channel */
    private $channel;

    /**
     * @var FilterService
     */
    private $filterService;

    /** images sizes available to resize into */
    private $imageSizes = [
        'original' => 'shop_api_product_original',
        'large' => 'shop_api_product_large',
        'medium' => 'shop_api_product_medium',
        'small' => 'shop_api_product_small',
        'tiny' => 'shop_api_product_tiny',
    ];

    /**
     * ProductService constructor.
     * @param ChannelContextInterface $channel
     * @param FilterService $filterService
     */
    public function __construct(ChannelContextInterface $channel, FilterService $filterService)
    {
        $this->channel = $channel;
        $this->filterService = $filterService;
    }

    public function serialize(Product $product)
    {
        $variant = $product->getVariants()[0];
        $images = [];

        /** @var ChannelInterface $channel */
        $channel = $this->channel->getChannel();

        foreach ($product->getImages() as $key => $image) {
            foreach ($this->imageSizes as $label => $imageSize) {
                $images[$key][$label] = $this->filterService->getUrlOfFilteredImage($image->getPath(), $imageSize);
            }
        }

        $product = [
            'id' => $product->getId(),
            'slug' => $product->getSlug(),
            'code' => $product->getCode(),
            'name' => $product->getName(),
            'images' => $images
        ];

        if ($variant instanceof ProductVariant) {
            $product['availability'] = $variant->getChannelPricingForChannel($channel);
        }

        return $product;
    }
}
