<?php

namespace App\Controller\AdminApi;

use App\Entity\Promotion\Promotion;
use App\Entity\Promotion\PromotionAction;
use Doctrine\ORM\QueryBuilder;
use App\Form\AdminApi\CouponType;
use App\Pagination\PaginationFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Promotion\PromotionCoupon;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Bundle\PromotionBundle\Doctrine\ORM\PromotionCouponRepository;

/**
 * CouponController
 * @Route("/coupons")
 */
class CouponController extends AbstractFOSRestController
{
    const ITEMS_PER_PAGE = 10;

    /** @var ChannelContextInterface $channelContext */
    private $channelContext;

    /**
     * @var PromotionCouponRepository
     */
    private $promotionCouponRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CouponController constructor.
     * @param ChannelContextInterface $channelContext
     * @param PromotionCouponRepository $promotionCouponRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ChannelContextInterface $channelContext,
        PromotionCouponRepository $promotionCouponRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->channelContext = $channelContext;
        $this->promotionCouponRepository = $promotionCouponRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="admin_api_coupons_index",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @param PaginationFactory $paginationFactory
     * @return Response
     */
    public function indexAction(Request $request, PaginationFactory $paginationFactory)
    {
        $statusCode = Response::HTTP_OK;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', self::ITEMS_PER_PAGE);
        $search = $request->query->get('search');

        $queryBuilder = $this->getQueryBuilder($search);
        $paginatedCollection = $paginationFactory->createCollection($queryBuilder, $search, $page, $limit, 'admin_api_coupons_index', [], 'Coupon list.', $statusCode, 'info');

        $list = [];
        foreach ($paginatedCollection->recordset as $coupon) {
            $list[] = $this->serializeCoupon($coupon);
        }
        $paginatedCollection->recordset = $list;

        $view = $this->view($paginatedCollection, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="admin_api_coupons_new",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request): Response
    {
        $form = $this->createForm(CouponType::class, null, ['validation_groups' => ['creation']]);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();
            $description = $form->get('description')->getData();
            $enabled = $form->get('enabled')->getData();
            $type = $form->get('type')->getData();
            $amount = (int) $form->get('amount')->getData();
            $oneUsagePerUser = $form->get('oneUsagePerUser')->getData();
            $limitUsageToXQuantityOfUsers = $form->get('limitUsageToXQuantityOfUsers')->getData();
            $usageLimit = $form->get('usageLimit')->getData();
            $startsAt = $form->get('startsAt')->getData();
            $endsAt = $form->get('endsAt')->getData();

            $coupon = $this->createCoupon($code);
            $coupon->setEnabled($enabled);
            $coupon = $this->updateCoupon($coupon, $description, $type, $amount, $oneUsagePerUser, $limitUsageToXQuantityOfUsers, $usageLimit, $startsAt, $endsAt);

            $this->entityManager->flush();
            $view = $this->view($this->serializeCoupon($coupon), Response::HTTP_CREATED);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_BAD_REQUEST;
        $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $form->getErrors(), 'code' => $statusCode], $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{code}.{_format}",
     *     name="admin_api_coupons_edit",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request): Response
    {
        $code = $request->get('code');
        $coupon = $this->promotionCouponRepository->findOneBy(['code' => $code]);

        if (!$coupon instanceof PromotionCoupon) {
            throw new NotFoundHttpException('Coupon not found');
        }

        $form = $this->createForm(CouponType::class, null, ['code' => $coupon->getCode(), 'validation_groups' => ['default']]);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $description = $form->get('description')->getData();
            $type = $form->get('type')->getData();
            $amount = $form->get('amount')->getData();
            $oneUsagePerUser = $form->get('oneUsagePerUser')->getData();
            $limitUsageToXQuantityOfUsers = $form->get('limitUsageToXQuantityOfUsers')->getData();
            $usageLimit = $form->get('usageLimit')->getData();
            $startsAt = $form->get('startsAt')->getData();
            $endsAt = $form->get('endsAt')->getData();

            $coupon = $this->updateCoupon($coupon, $description, $type, $amount, $oneUsagePerUser, $limitUsageToXQuantityOfUsers, $usageLimit, $startsAt, $endsAt);
            $this->entityManager->flush();
            $view = $this->view($this->serializeCoupon($coupon), Response::HTTP_OK);

            return $this->handleView($view);
        }

        $statusCode = Response::HTTP_BAD_REQUEST;
        $view = $this->view(['type' => 'error', 'message' => 'Invalid form.', 'recordset' => $form->getErrors(), 'code' => $statusCode], $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{code}/enable.{_format}",
     *     name="admin_api_coupons_enable",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function enableAction(Request $request): Response
    {
        $code = $request->get('code');
        $coupon = $this->promotionCouponRepository->findOneBy(['code' => $code]);

        if (!$coupon instanceof PromotionCoupon) {
            throw new NotFoundHttpException('Coupon not found');
        }

        $coupon->setEnabled(true);

        try {
            $this->entityManager->flush();
            $statusCode = Response::HTTP_OK;
            $view = $this->view($this->serializeCoupon($coupon), $statusCode);

            return $this->handleView($view);

        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $view = $this->view(['type' => 'error', 'message' => $exception->getMessage(), 'recordset' => [], 'code' => $statusCode], $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @Route(
     *     "/{code}/disable.{_format}",
     *     name="admin_api_coupons_disable",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function disableAction(Request $request): Response
    {
        $code = $request->get('code');
        $coupon = $this->promotionCouponRepository->findOneBy(['code' => $code]);

        if (!$coupon instanceof PromotionCoupon) {
            throw new NotFoundHttpException('Coupon not found');
        }

        $coupon->setEnabled(false);

        try {
            $this->entityManager->flush();
            $statusCode = Response::HTTP_OK;
            $view = $this->view($this->serializeCoupon($coupon), $statusCode);

            return $this->handleView($view);

        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $view = $this->view(['type' => 'error', 'message' => $exception->getMessage(), 'recordset' => [], 'code' => $statusCode], $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @Route(
     *     "/{code}.{_format}",
     *     name="admin_api_coupons_remove",
     *     methods={"DELETE"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request): Response
    {
        $code = $request->get('code');
        $coupon = $this->promotionCouponRepository->findOneBy(['code' => $code]);

        if (!$coupon instanceof PromotionCoupon) {
            throw new NotFoundHttpException('Coupon not found');
        }

        $this->entityManager->remove($coupon->getPromotion());
        $this->entityManager->remove($coupon);

        try {
            $this->entityManager->flush();
            $statusCode = Response::HTTP_NO_CONTENT;
            $view = $this->view([], $statusCode);

            return $this->handleView($view);

        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $view = $this->view(['type' => 'error', 'message' => $exception->getMessage(), 'recordset' => [], 'code' => $statusCode], $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @param PromotionCoupon|null $coupon
     * @return array
     */
    private function serializeCoupon(?PromotionCoupon $coupon): array
    {
        if (null == $coupon) {
            return [];
        }

        return [
            'id' => $coupon->getId(),
            'code' => $coupon->getCode(),
            'description' => $coupon->getPromotion()->getDescription(),
            'enabled' => $coupon->isEnabled(),
            'type' => $coupon->getTypeSlug($this->channelContext->getChannel()->getCode()),
            'amount' => $coupon->getValue($this->channelContext->getChannel()->getCode()),
            'one_usage_per_user' => ($coupon->getPerCustomerUsageLimit() != PromotionCoupon::MAX_USAGES_PER_USER),
            'limit_usage_to_x_quantity_of_users' => ($coupon && $coupon->getUsageLimit()),
            'usage_limit' => $coupon->getUsageLimit(),
            'used' => $coupon->getUsed(),
            'starts_at' => $coupon->getPromotion()->getStartsAt(),
            'ends_at' => $coupon->getPromotion()->getEndsAt(),
        ];
    }

    /**
     * @param $search
     * @return QueryBuilder
     */
    private function getQueryBuilder($search)
    {
        $queryBuilder = $this->promotionCouponRepository
            ->createQueryBuilder('coupon')
            ->leftJoin('coupon.promotion', 'promotion')
            ->orderBy('coupon.createdAt', 'DESC');

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('coupon.code LIKE :search OR promotion.description LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $queryBuilder;
    }

    /**
     * @param $code
     * @return PromotionCoupon|null
     */
    private function createCoupon($code): ?PromotionCoupon
    {
        $promotion = $this->promotionCouponRepository->findOneBy(['code' => $code]);

        if ($promotion instanceof PromotionCoupon) {
            throw new BadRequestHttpException('Coupon already exists');
        }

        /** Promotion */
        $promotion = new Promotion();
        $promotion->setCode($code);
        $promotion->setName($code);
        $promotion->setCouponBased(true);
        $promotion->setExclusive(true);
        $promotion->setName($promotion->getCode());
        $promotion->addChannel($this->channelContext->getChannel());

        $this->entityManager->persist($promotion);

        /** coupon */
        $coupon = new PromotionCoupon();
        $coupon->setPromotion($promotion);
        $coupon->setCode($promotion->getCode());
        $coupon->setReusableFromCancelledOrders(true);

        $this->entityManager->persist($coupon);

        return $coupon;
    }

    /**
     * @param PromotionCoupon|null $coupon
     * @param $description
     * @param $type
     * @param $amount
     * @param $oneUsagePerUser
     * @param $limitUsageToXQuantityOfUsers
     * @param $usageLimit
     * @param $startsAt
     * @param $endsAt
     * @return PromotionCoupon|null
     */
    private function updateCoupon(?PromotionCoupon $coupon, $description, $type, $amount, $oneUsagePerUser, $limitUsageToXQuantityOfUsers, $usageLimit, $startsAt, $endsAt)
    {
        $perCustomerUsageLimit = PromotionCoupon::MAX_USAGES_PER_USER;
        $restrictedUsagePerCustomer = isset($oneUsagePerUser) ? filter_var($oneUsagePerUser, FILTER_VALIDATE_BOOLEAN) : false;

        if ($restrictedUsagePerCustomer) {
            $perCustomerUsageLimit = 1;
        }

        $promotion = $coupon->getPromotion();

        if (isset($usageLimit)) {
            $promotion->setUsageLimit($usageLimit);
        }

        if (isset($description)) {
            $promotion->setDescription($description);
        }

        if (isset($startsAt)) {
            $promotion->setStartsAt($startsAt);
        }

        if (isset($endsAt)) {
            $promotion->setEndsAt($endsAt);
        }

        if (isset($limitUsageToXQuantityOfUsers)) {
            $promotion->setUsageLimit($limitUsageToXQuantityOfUsers);
        }

        $coupon->setUsageLimit($promotion->getUsageLimit());
        $coupon->setExpiresAt($promotion->getEndsAt());

        if (isset($perCustomerUsageLimit)) {
            $coupon->setPerCustomerUsageLimit($perCustomerUsageLimit);
        }

        if (isset($limitUsageToXQuantityOfUsers)) {
            $coupon->setUsageLimit($limitUsageToXQuantityOfUsers);
        }

        if (isset($type) && isset($amount)) {
            /** Add Action */
            $promotionAction = $promotion->getActions()[0] ? $promotion->getActions()[0] : new PromotionAction();
            $promotionAction->setPromotion($promotion);
            $promotionAction->setType($type);

            if ($type == PromotionCoupon::TYPE_FIXED_AMOUNT) {
                $promotionAction->setConfiguration([$this->channelContext->getChannel()->getCode() => ['amount' => $amount * 100]]);
            }

            if ($type == PromotionCoupon::TYPE_PERCENTAGE) {
                $promotionAction->setConfiguration(['percentage' => $amount / 100]);
            }

            $promotion->addAction($promotionAction);
            $this->entityManager->persist($promotionAction);
        }

        return $coupon;
    }
}
