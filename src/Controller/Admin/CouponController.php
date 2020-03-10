<?php

namespace App\Controller\Admin;

use App\Entity\Promotion\Promotion;
use App\Entity\Promotion\PromotionAction;
use App\Entity\Promotion\PromotionCoupon;
use App\Form\Admin\PromotionType;
use Psr\Log\LoggerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
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
     * @param ChannelContextInterface $channelContext
     * @return Response
     */
    public function indexAction(Request $request, ChannelContextInterface $channelContext)
    {
        $filter = $request->query->get('filter');
        $page = $request->query->getInt('page', 1);

        return $this->render('/admin/coupon/index.html.twig', [
            'pagination' => $this->getCouponsPagination($filter, $page),
            'total' => $this->countActiveCoupons(),
            'channel' => $channelContext->getChannel()
        ]);
    }

    /**
     * New coupon.
     * @Route("/coupon/new", name="coupons_new")
     * @param Request $request
     * @param ChannelContextInterface $channelContext
     * @return Response
     */
    public function newAction(Request $request, ChannelContextInterface $channelContext)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $promotion = new Promotion();
        $promotion->setCouponBased(true);
        $promotion->setExclusive(true);

        // additional data
        $formData = $request->get('promotion');
        $type = $formData['type'];
        $amount = $formData['amount'];
        $perCustomerUsageLimit = PromotionCoupon::MAX_USAGES_PER_USER;
        $usageLimit = $formData['usageLimit'] ? $formData['usageLimit'] : null;
        $restrictedUsagePerCustomer = isset($formData['oneUsagePerUser']) ? filter_var($formData['oneUsagePerUser'], FILTER_VALIDATE_BOOLEAN) : false;

        if ($restrictedUsagePerCustomer) {
            $perCustomerUsageLimit = 1;
        }

        $exist = $entityManager->getRepository('App:Promotion\PromotionCoupon')->findOneBy(['code' => $formData['code']]);

        if ($exist instanceof PromotionCoupon) {
            $promotion->setCodeAlreadyInUse(true);
        }

        $form = $this->createForm(PromotionType::class, $promotion, ['channel' => $channelContext->getChannel()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promotion->setUsageLimit($usageLimit);
            $promotion->setName($promotion->getCode());
            $promotion->addChannel($channelContext->getChannel());

            $entityManager->persist($promotion);

            /** Add Action */
            $promotionAction = new PromotionAction();
            $promotionAction->setPromotion($promotion);
            $promotionAction->setType($type);

            if ($type == PromotionCoupon::TYPE_FIXED_AMOUNT) {
                $promotionAction->setConfiguration([$channelContext->getChannel()->getCode() => ['amount' => $amount * 100]]);
            }

            if ($type == PromotionCoupon::TYPE_PERCENTAGE) {
                $promotionAction->setConfiguration(['percentage' => $amount / 100]);
            }

            $entityManager->persist($promotionAction);

            /** coupon */
            $coupon = new PromotionCoupon();
            $coupon->setPromotion($promotion);
            $coupon->setCode($promotion->getCode());
            $coupon->setUsageLimit($promotion->getUsageLimit());
            $coupon->setExpiresAt($promotion->getEndsAt());
            $coupon->setPerCustomerUsageLimit($perCustomerUsageLimit);
            $coupon->setReusableFromCancelledOrders(true);
            $coupon->setEnabled(false);

            $entityManager->persist($coupon);

            $entityManager->flush();

            return $this->redirectToRoute('coupons_index');
        }

        return $this->render('/admin/coupon/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Show promotion code
     * @Route("/coupon/{id}", name="coupons_show", methods={"GET"})
     * @param Request $request
     * @param ChannelContextInterface $channelContext
     * @return Response
     */
    public function showAction(Request $request, ChannelContextInterface $channelContext)
    {
        $id = $request->get('id');

        $manager = $this->get('doctrine')->getManager();
        /** @var PromotionCoupon $coupon */
        $coupon = $manager->getRepository('App:Promotion\PromotionCoupon')->find($id);

        return $this->render('/admin/coupon/show.html.twig', [
            'coupon' => $coupon,
            'channel' => $channelContext->getChannel()->getCode()
        ]);
    }

    /**
     * Edit promotion code
     * @Route("/coupon/{id}/edit", name="coupons_edit")
     * @param Request $request
     * @param ChannelContextInterface $channelContext
     * @return Response
     */
    public function editAction(Request $request, ChannelContextInterface $channelContext)
    {
        $id = $request->get('id');
        $entityManager = $this->getDoctrine()->getManager();

        $manager = $this->get('doctrine')->getManager();
        /** @var PromotionCoupon $coupon */
        $coupon = $manager->getRepository('App:Promotion\PromotionCoupon')->find($id);
        $promotion = $coupon->getPromotion();

        // additional data
        $formData = $request->get('promotion');
        $type = $formData['type'];
        $amount = $formData['amount'];
        $perCustomerUsageLimit = PromotionCoupon::MAX_USAGES_PER_USER;
        $usageLimit = $formData['usageLimit'] ? $formData['usageLimit'] : null;
        $restrictedUsagePerCustomer = isset($formData['oneUsagePerUser']) ? filter_var($formData['oneUsagePerUser'], FILTER_VALIDATE_BOOLEAN) : false;

        if ($restrictedUsagePerCustomer) {
            $perCustomerUsageLimit = 1;
        }

        $exist = $entityManager->getRepository('App:Promotion\PromotionCoupon')->findOneBy(['code' => $formData['code']]);

        if ($exist instanceof PromotionCoupon && $exist->getId() != $coupon->getId()) {
            $promotion->setCodeAlreadyInUse(true);
        }

        $form = $this->createForm(PromotionType::class, $promotion, ['channel' => $channelContext->getChannel()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promotion->setUsageLimit($usageLimit);
            $promotion->setName($promotion->getCode());

            $entityManager->flush();

            $promotionAction = $promotion->getActions()[0];
            $promotionAction->setType($type);

            if ($type == PromotionCoupon::TYPE_FIXED_AMOUNT) {
                $promotionAction->setConfiguration([$channelContext->getChannel()->getCode() => ['amount' => $amount * 100]]);
            }

            if ($type == PromotionCoupon::TYPE_PERCENTAGE) {
                $promotionAction->setConfiguration(['percentage' => $amount / 100]);
            }

            /** coupon */
            $coupon = $promotion->getCoupons()[0];
            $coupon->setCode($promotion->getCode());
            $coupon->setUsageLimit($promotion->getUsageLimit());
            $coupon->setExpiresAt($promotion->getEndsAt());
            $coupon->setPerCustomerUsageLimit($perCustomerUsageLimit);
            $coupon->setReusableFromCancelledOrders(true);

            $entityManager->flush();

            return $this->redirectToRoute('coupons_index');
        }

        return $this->render('/admin/coupon/edit.html.twig', [
            'coupon' => $coupon,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Toggle the enable status on promotion code
     * @Route("/coupon/{id}/toggle-status", name="coupons_toggle_status", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function toggleEnabledAction(Request $request)
    {
        $id = $request->get('id');

        $manager = $this->get('doctrine')->getManager();
        $coupon = $manager->getRepository('App:Promotion\PromotionCoupon')->find($id);
        $coupon->setEnabled(!$coupon->isEnabled());

        try {
            $manager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Delete promotion code
     * @Route("/coupon/{id}", name="coupons_delete", methods={"DELETE"})
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $id = $request->get('id');

        $manager = $this->get('doctrine')->getManager();
        /** @var PromotionCoupon $coupon */
        $coupon = $manager->getRepository('App:Promotion\PromotionCoupon')->find($id);

        $manager->remove($coupon->getPromotion());
        $manager->remove($coupon);

        try {
            $manager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
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
                ->andWhere('coupon.expiresAt IS NULL')
                ->andWhere('coupon.expiresAt > :now')
                ->andWhere('coupon.enabled = :enabled')
                ->setParameter('now', $now)
                ->setParameter('enabled', true)
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
