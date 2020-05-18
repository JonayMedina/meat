<?php

namespace App\Controller\ShopApi;

use App\Entity\Promotion\Promotion;
use App\Entity\Promotion\PromotionCoupon;
use App\Model\APIResponse;
use App\Entity\Order\Order;
use App\Entity\User\ShopUser;
use Sylius\Bundle\PromotionBundle\Doctrine\ORM\PromotionCouponRepository;
use Sylius\Bundle\PromotionBundle\Doctrine\ORM\PromotionRepository;
use Sylius\Component\Promotion\Repository\PromotionCouponRepositoryInterface;
use Sylius\Component\Promotion\Repository\PromotionRepositoryInterface;
use Sylius\ShopApiPlugin\Controller\Cart\AddCouponAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Core\Model\OrderInterface;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * CartController constructor.
     * @param OrderRepository $repository
     * @param PromotionCouponRepository $couponRepository
     * @param AddCouponAction $addCouponAction
     * @param TranslatorInterface $translator
     */
    public function __construct(OrderRepository $repository, PromotionCouponRepository $couponRepository, AddCouponAction $addCouponAction, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->couponRepository = $couponRepository;
        $this->addCouponAction = $addCouponAction;
        $this->translator = $translator;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_get_carts",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $list = [];
        $statusCode = Response::HTTP_OK;

        /** @var ShopUser $user */
        $user = $this->getUser();

        if ($user instanceof ShopUser) {
            /** @var Order[] $orders */
            $orders = $this->repository
                ->createQueryBuilder('o')
                ->andWhere('o.customer = :customer')
                ->andWhere('o.tokenValue IS NOT NULL')
                ->andWhere('o.state = :state')
                ->setParameter('customer', $user->getCustomer())
                ->setParameter('state', OrderInterface::STATE_CART)
                ->getQuery()
                ->getResult();

            foreach ($orders as $order) {
                $list[] = [
                    'id' => $order->getId(),
                    'number' => $order->getNumber(),
                    'token_value' => $order->getTokenValue(),
                ];
            }
        } else {
            $list = [];
        }

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Cart list', $list);
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
}
