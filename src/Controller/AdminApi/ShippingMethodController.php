<?php

namespace App\Controller\AdminApi;

use App\Entity\Locale\Locale;
use App\Entity\Shipping\ShippingMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Shipping\Calculator\DefaultCalculators;
use Sylius\Component\Channel\Context\ChannelContextInterface;

/**
 * ShippingMethodController
 * @Route("/shipping-method")
 */
class ShippingMethodController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * ShippingMethodController constructor.
     * @param EntityManagerInterface $entityManager
     * @param ChannelContextInterface $channelContext
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelContextInterface $channelContext
    )
    {
        $this->entityManager = $entityManager;
        $this->channelContext = $channelContext;
    }

    /**
     * @Route(
     *     "/update.{_format}",
     *     name="admin_api_update_shipping_method",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function updateAction(Request $request)
    {
        $amount = $request->get('amount', 0);

        if (!is_numeric($amount) || $amount < 0) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $view = $this->view(['type' => 'error', 'message' => 'Invalid amount sent.', 'recordset' => [], 'code' => $statusCode], $statusCode);

            return $this->handleView($view);
        }

        /**
         * @var ShippingMethod $shippingMethod
         */
        $shippingMethod = $this->entityManager->getRepository('App:Shipping\ShippingMethod')
            ->findOneBy(['code' => ShippingMethod::DEFAULT_SHIPPING_METHOD]);

        if (!$shippingMethod) {
            $zone = $this->entityManager->getRepository('App:Addressing\Zone')
                ->findOneBy(['code' => 'GT']);

            if (!$zone) {
                $zone = $this->entityManager->getRepository('App:Addressing\Zone')
                    ->findOneBy([]);
            }

            $shippingMethod = new ShippingMethod();
            $shippingMethod->setEnabled(true);
            $shippingMethod->setCode(ShippingMethod::DEFAULT_SHIPPING_METHOD);
            $shippingMethod->setCurrentLocale(Locale::DEFAULT_LOCALE);
            $shippingMethod->setName('Envíos de Meat House');
            $shippingMethod->setDescription('');
            $shippingMethod->setPosition(0);
            $shippingMethod->setCalculator(DefaultCalculators::FLAT_RATE);
            $shippingMethod->setZone($zone);

            $this->entityManager->persist($shippingMethod);
        }


        $shippingMethod->addChannel($this->channelContext->getChannel());
        $shippingMethod->setConfiguration([
            $this->channelContext->getChannel()->getCode() => [
                'amount' => (int)$amount
            ]
        ]);

        $this->entityManager->flush();
        $view = $this->view($this->serializeShippingMethod($shippingMethod), Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * @param ShippingMethod $shippingMethod
     * @return array
     */
    private function serializeShippingMethod(ShippingMethod $shippingMethod)
    {
        return [
            'id' => $shippingMethod->getId(),
            'name' => $shippingMethod->getName(),
            'description' => $shippingMethod->getDescription(),
            'amount' => $shippingMethod->getConfiguration()[$this->channelContext->getChannel()->getCode()]['amount'] ?? null,
        ];
    }
}
