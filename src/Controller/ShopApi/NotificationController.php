<?php

namespace App\Controller\ShopApi;

use App\Entity\Notification;
use App\Entity\Product\Product;
use App\Entity\Product\ProductVariant;
use App\Entity\PushNotification;
use App\Service\ProductService;
use Psr\Log\LoggerInterface;
use App\Pagination\PaginationFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * LocationController
 * @Route("/notifications")
 */
class NotificationController extends AbstractFOSRestController
{
    const ITEMS_PER_PAGE = 10;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var NotificationRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductService
     */
    private $productService;

    /**
     * NotificationController constructor.
     * @param NotificationRepository $repository
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(NotificationRepository $repository, EntityManagerInterface $entityManager, LoggerInterface $logger, ProductService $productService)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->logger = $logger;
        $this->productService = $productService;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_notifications",
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

        $queryBuilder = $this->repository
            ->createQueryBuilder('notification')
            ->andWhere('notification.user = :user')
            ->setParameter('user', $this->getUser());

        $list = [];
        $paginatedCollection = $paginationFactory->createCollection($queryBuilder, '', $page, $limit, 'shop_api_notifications', [], 'Notification list.', $statusCode, 'info');

        foreach ($paginatedCollection->recordset as $item) {
            /** @var Notification $item */
            $list[] = $this->serializeNotification($item);
        }

        $paginatedCollection->recordset = $list;

        $view = $this->view($paginatedCollection, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="shop_api_show_notifications",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $id = $request->get('id');
        $notification = $this->repository->find($id);
        $user = $this->getUser();

        if (!$notification instanceof Notification) {
            throw new NotFoundHttpException('Not found.');
        }

        if ($notification->getUser() != $user) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $statusCode = Response::HTTP_OK;
        $this->entityManager->flush();
        $view = $this->view($this->serializeNotification($notification), $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="shop_api_touch_notifications",
     *     methods={"PUT"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function touchAction(Request $request)
    {
        $id = $request->get('id');
        $notification = $this->repository->find($id);
        $user = $this->getUser();

        if (!$notification instanceof Notification) {
            throw new NotFoundHttpException('Not found.');
        }

        if ($notification->getUser() != $user) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $notification->setSeen(true);

        try {
            $statusCode = Response::HTTP_OK;
            $this->entityManager->flush();
            $view = $this->view([], $statusCode);

            return $this->handleView($view);
        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $this->entityManager->flush();
            $view = $this->view(['message' => $exception->getMessage()], $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="shop_api_delete_notifications",
     *     methods={"DELETE"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $id = $request->get('id');
        $notification = $this->repository->find($id);
        $user = $this->getUser();

        if (!$notification instanceof Notification) {
            throw new NotFoundHttpException('Not found.');
        }

        if ($notification->getUser() != $user) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $this->entityManager->remove($notification);

        try {
            $statusCode = Response::HTTP_NO_CONTENT;
            $this->entityManager->flush();
            $view = $this->view([], $statusCode);

            return $this->handleView($view);
        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $this->entityManager->flush();
            $view = $this->view(['message' => $exception->getMessage()], $statusCode);

            return $this->handleView($view);
        }
    }

    private function serializeNotification(Notification $notification)
    {
        $object = [
            'id' => $notification->getId(),
            'title' => $notification->getTitle(),
            'text' => $notification->getText(),
            'seen' => $notification->isSeen(),
            'type' => $notification->getType(),

        ];

        /** @var PushNotification $pushNotification */
        $pushNotification = $notification->getPushNotification();

        if ($notification->getType() == PushNotification::TYPE_PROMOTION && $pushNotification->getPromotionType() == PushNotification::PROMOTION_TYPE_BANNER) {
            $banner = $pushNotification->getPromotionBanner();
            $promotionObject = null;

            if ($banner->getProductVariant() instanceof ProductVariant) {
                /** @var Product $product */
                $product = $banner->getProductVariant()->getProduct();
                $promotionObject = $this->productService->serialize($product);
            }

            $object['promotion_banner'] = [
                'id' => $banner->getId(),
                'name' => $banner->getName(),
                'starts_at' => $banner->getStartDate() ? $banner->getStartDate()->format('c') : null,
                'ends_at' => $banner->getEndDate() ? $banner->getEndDate()->format('c') : null,
                'product' => $promotionObject,
            ];
        }

        if ($notification->getType() == PushNotification::TYPE_PROMOTION && $pushNotification->getPromotionType() == PushNotification::PROMOTION_TYPE_COUPON) {
            $coupon = $pushNotification->getPromotionCoupon();

            $object['coupon'] = [
                'id' => $coupon->getId(),
                'code' => $coupon->getCode(),
                'usage_limit' => $coupon->getUsageLimit(),
                'used' => $coupon->getUsed(),
                'expires_at' => $coupon->getExpiresAt() ? $coupon->getExpiresAt()->format('c') : null,
                'per_customer_usage_limit' => $coupon->getPerCustomerUsageLimit(),
                'enabled' => $coupon->isEnabled(),
            ];
        }

        return $object;
    }
}
