<?php

namespace App\Controller\Shop;

use FOS\RestBundle\View\View;
use App\Entity\Product\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Resource\ResourceActions;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;

class ProductExtendedController extends ResourceController
{
    public function showAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $this->isGrantedOr403($configuration, ResourceActions::SHOW);

        $resource = $this->findOr404($configuration);
        $this->eventDispatcher->dispatch(ResourceActions::SHOW, $configuration, $resource);

        $view = View::create($resource);

        if ($configuration->isHtmlRequest()) {
            $view
                ->setTemplate($configuration->getTemplate(ResourceActions::SHOW . '.html'))
                ->setTemplateVar($this->metadata->getName())
                ->setData([
                    'configuration' => $configuration,
                    'metadata' => $this->metadata,
                    'resource' => $resource,
                    $this->metadata->getName() => $resource,
                ])
            ;
        }

        return $this->viewHandler->handle($configuration, $view);
    }

    /**
     * @param RequestConfiguration $configuration
     * @return ResourceInterface
     */
    protected function findOr404(RequestConfiguration $configuration): ResourceInterface
    {
        // Check if product is associated to channel, if not then create the relation
        $this->isInChannel($configuration->getRequest()->attributes->get('slug'));

        $this->singleResourceProvider->get($configuration, $this->repository);

        if (null === $resource = $this->singleResourceProvider->get($configuration, $this->repository)) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $this->metadata->getHumanizedName()));
        }

        return $resource;
    }

    /**
     * If product is not in channel we need to create the relation
     *
     * @param $slug
     */
    protected function isInChannel($slug) {
        $em = $this->getDoctrine()->getManager();
        /** @var ChannelRepositoryInterface $channelRepo */
        $channelRepo = $this->getDoctrine()->getRepository('App:Channel\Channel');

        /** @var ProductRepositoryInterface $productRepo */
        $productRepo = $this->repository;
        $channel = $channelRepo->findLatest();
        $product = $productRepo->findOneBy(['code' => $slug]);

        if ($product instanceof Product) {
            if ($product->getChannels()->count() <= 0) {
                $product->addChannel($channel);

                $em->flush();
            }
        }
    }
}
