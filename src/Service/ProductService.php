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

    /**
     * @var string
     */
    private $appUrl;

    /**
     * ProductService constructor.
     * @param ChannelContextInterface $channel
     * @param FilterService $filterService
     * @param string $appUrl
     */
    public function __construct(
        ChannelContextInterface $channel,
        FilterService $filterService,
        $appUrl
    ) {
        $this->channel = $channel;
        $this->filterService = $filterService;
        $this->appUrl = $appUrl;
    }

    public function serialize(Product $product)
    {
        $variant = $product->getVariants()[0];
        $images = [];

        /** @var ChannelInterface $channel */
        $channel = $this->channel->getChannel();

        foreach ($product->getImages() as $key => $image) {
            $images[$key] = $this->appUrl . '/media/cache/resolve/mobile_thumbnail/'. $image->getPath();
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
