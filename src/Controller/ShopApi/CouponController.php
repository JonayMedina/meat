<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Promotion\PromotionCoupon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Bundle\PromotionBundle\Doctrine\ORM\PromotionCouponRepository;

use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * CouponController
 * @Route("/coupons")
 */
class CouponController extends AbstractFOSRestController
{
    /**
     * @var PromotionCouponRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Coupon constructor.
     * @param PromotionCouponRepository $repository
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(PromotionCouponRepository $repository, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(
     *     "/{code}.{_format}",
     *     name="shop_api_get_coupon",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $code = $request->get('code');
        $coupon = $this->getPromotionCoupon($code);

        if (!$coupon instanceof PromotionCoupon) {
            /** Not found */
            $statusCode = Response::HTTP_NOT_FOUND;
            $recordset = [];

            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Not found.', $recordset);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_OK;
        $recordset = [
            'code' => $coupon->getCode(),
            'limit' => $coupon->getUsageLimit(),
            'used' => $coupon->getUsed(),
            'expires_at' => $coupon->getExpiresAt(),
        ];

        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Coupon code.', $recordset);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @param $code
     * @return PromotionCoupon|null
     */
    private function getPromotionCoupon($code): ?PromotionCoupon
    {
        try {
            /** @var PromotionCoupon $coupon */
            return $this->repository->createQueryBuilder('coupon')
                ->innerJoin('coupon.promotion', 'promotion')
                ->andWhere('coupon.code = :code')
                ->andWhere('coupon.enabled = :enabled')
                ->andWhere('promotion.couponBased = :couponBased')
                ->setParameter('code', $code)
                ->setParameter('enabled', true)
                ->setParameter('couponBased', true)
                ->getQuery()
                ->getOneOrNullResult();

        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }
}
