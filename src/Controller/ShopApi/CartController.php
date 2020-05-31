<?php

namespace App\Controller\ShopApi;

use App\Entity\Addressing\Address;
use App\Entity\Customer\Customer;
use Carbon\Carbon;
use App\Model\APIResponse;
use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use App\Service\OrderService;
use App\Service\PaymentGatewayService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Promotion\PromotionCoupon;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\AddressRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\ShopApiPlugin\Controller\Cart\AddCouponAction;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Sylius\Bundle\PromotionBundle\Doctrine\ORM\PromotionCouponRepository;

/**
 * CartController
 * @Route("/carts")
 */
class CartController extends AbstractFOSRestController
{
    /**
     * @var OrderRepository
     */
    private $repository;

    /** @var PromotionCouponRepository */
    private $couponRepository;

    /** @var AddCouponAction */
    private $addCouponAction;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * CartController constructor.
     * @param OrderRepository $repository
     * @param PromotionCouponRepository $couponRepository
     * @param AddCouponAction $addCouponAction
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param OrderService $orderService
     * @param AddressRepository $addressRepository
     */
    public function __construct(OrderRepository $repository, PromotionCouponRepository $couponRepository, AddCouponAction $addCouponAction, TranslatorInterface $translator, EntityManagerInterface $entityManager, OrderService $orderService, AddressRepository $addressRepository)
    {
        $this->repository = $repository;
        $this->couponRepository = $couponRepository;
        $this->addCouponAction = $addCouponAction;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->orderService = $orderService;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_get_cart",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        /** @var ShopUser $user */
        $user = $this->getUser();

        try  {
            $mainOrder = $this->orderService->mergeCarts($user);

            $statusCode = Response::HTTP_OK;
            $serialized = $this->orderService->serializeOrder($mainOrder);
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Merged...', $serialized);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);

        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $exception->getMessage());
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @Route(
     *     "/{token}/address.{_format}",
     *     name="shop_api_cart_add_address",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function addAddressAction(Request $request, $token)
    {
        $statusCode = Response::HTTP_OK;
        $response = [];

        $addressId = $request->get('address_id');
        $type = $request->get('type');

        $cart = $this->repository->findOneBy(['tokenValue' => $token]);
        /** @var Address $address */
        $address = $this->addressRepository->find($addressId);

        if (!in_array($type, [Address::TYPE_BILLING, Address::TYPE_SHIPPING])) {
            throw new BadRequestHttpException('Bad address type');
        }

        if (!$cart instanceof Order) {
            throw new NotFoundHttpException('Cart not found.');
        }

        /** @var Customer $customer */
        $customer = $cart->getCustomer();

        if (!$address instanceof Address || $address->getCustomer() !== $customer) {
            throw new NotFoundHttpException('Address not found.');
        }

        $addressCloned = clone $address;
        $addressCloned->setCustomer(null);

        if (Address::TYPE_BILLING == $type) {
            $cart->setBillingAddress($addressCloned);
        }

        if (Address::TYPE_SHIPPING == $type) {
            $cart->setShippingAddress($addressCloned);
        }

        $this->entityManager->persist($addressCloned);
        $this->entityManager->flush();

        $response = $this->orderService->serializeOrder($cart);

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{token}/coupon.{_format}",
     *     name="shop_api_cart_add_coupon",
     *     methods={"GET"}
     * )
     * @param Request $request
     * @param string $token
     * @return Response
     */
    public function addCouponAction(Request $request, $token) {
        $code = $request->get('coupon');
        $cart = $this->repository->findOneBy(['tokenValue' => $token]);
        $coupon = $this->couponRepository->findOneBy(['code' => $code]);

        if ($cart instanceof Order) {
            if ($coupon instanceof PromotionCoupon) {
                if ($coupon->isEnabled()) {
                    return $this->addCouponAction->__invoke($request);
                } else {
                    $statusCode = Response::HTTP_BAD_REQUEST;
                    $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.coupon_not_found'));
                }
            } else {
                $statusCode = Response::HTTP_NOT_FOUND;
                $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.coupon_not_found'));
            }
        } else {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $this->translator->trans('app.api.cart.not_found'));
        }

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{token}/complete.{_format}",
     *     name="shop_api_pay_cart",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @param PaymentGatewayService $paymentService
     * @return Response
     */
    public function payAction(Request $request, PaymentGatewayService $paymentService) {
        $token = $request->get('token');
        $statusCode = Response::HTTP_OK;

        /** @var Order $order */
        $order = $this->repository->findOneBy(['tokenValue' => $token]);

        $cardHolder = $request->get('card_holder');
        $cardNumber = $request->get('card_number');
        $expDate = $request->get('exp_date');
        $cvv = $request->get('cvv');

        $result = $paymentService->orderPayment($order, $cardHolder, $cardNumber, $expDate, $cvv);

        if ('00' !== $result['responseCode']) {
            $statusCode = Response::HTTP_BAD_REQUEST;
        }

        $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $result['responseMessage'], $result);

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{token}/schedule-my-delivery.{_format}",
     *     name="shop_api_pay_cart",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function scheduleMyDeliveryAction(Request $request) {
        $token = $request->get('token');

        $statusCode = Response::HTTP_OK;
        $preferredDeliveryDate = $request->get('preferred_delivery_date');
        $scheduledDeliveryDate = $request->get('scheduled_delivery_date');

        $nextAvailableDay = $this->orderService->getNextAvailableDay($preferredDeliveryDate, $scheduledDeliveryDate);

        /** @var Order $order */
        $order = $this->repository->findOneBy(['tokenValue' => $token]);

        $order->setEstimatedDeliveryDate($nextAvailableDay);
        $order->setScheduledDeliveryDate(Carbon::parse($scheduledDeliveryDate));
        $this->entityManager->flush();

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Ok', [
            'token' => $order->getTokenValue(),
            'estimated_delivery_date' => $order->getEstimatedDeliveryDate()
        ]);

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }
}
