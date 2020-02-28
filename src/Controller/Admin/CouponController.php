<?php

namespace App\Controller\Admin;

use Psr\Log\LoggerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class CouponController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class CouponController extends AbstractController
{
    const PAGINATOR_LIMIT = 10;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var PaginatorInterface $paginator
     */
    private $paginator;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param PaginatorInterface $paginator
     */
    public function __construct(LoggerInterface $logger, PaginatorInterface $paginator)
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
    }

    /**
     *
     * @Route("/coupon", name="coupons_index")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $filter = $request->query->get('filter');
        $page = $request->query->getInt('page', 1);

        return $this->render('/admin/coupon/index.html.twig', [
            'pagination' => $this->getCouponsPagination($filter, $page),
            'total' => $this->countActiveCoupons(),
        ]);
    }

    /**
     * Return total of active coupons.
     * @return int|null
     */
    private function countActiveCoupons(): ?int
    {
        $now = date('Y-m-d H:i:s');

        try {
            return $queryBuilder = $this->get('doctrine')->getManager()->getRepository('App:Promotion\PromotionCoupon')
                ->createQueryBuilder('coupon')
                ->select('COUNT(coupon)')
                ->andWhere('coupon.expiresAt IS NULL OR coupon.expiresAt > :now')
                ->setParameter('now', $now)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }

    /**
     * Return coupons.
     * @param $filter
     * @param int $page
     * @return PaginationInterface|null
     */
    private function getCouponsPagination($filter, $page = 1)
    {
        try {
            $queryBuilder = $this->get('doctrine')->getManager()->getRepository('App:Promotion\PromotionCoupon')
                ->createQueryBuilder('coupon');

            if (!empty($filter)) {
                $queryBuilder
                    ->andWhere('coupon.code LIKE :filter')
                    ->setParameter('filter', '%'.$filter.'%');
            }

            $queryBuilder
                ->orderBy('coupon.createdAt', 'DESC');

            return $this->paginator->paginate(
                $queryBuilder,
                $page,
                self::PAGINATOR_LIMIT
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }
}
