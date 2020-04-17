<?php

namespace App\Controller\Admin;


use App\Entity\PushNotification;
use Psr\Log\LoggerInterface;
use App\Repository\SegmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PushNotificationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PushNotificationController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class PushNotificationController extends AbstractController
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SegmentRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     * @param PushNotificationRepository $repository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LoggerInterface $logger, PaginatorInterface $paginator, TranslatorInterface $translator, PushNotificationRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @Route("/push", name="push_index")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $filter = $request->query->get('filter');
        $page = $request->query->getInt('page', 1);

        $queryBuilder = $this->repository
            ->createQueryBuilder('push_notification');

        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('push_notification.title LIKE :filter OR push_notification.description LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        $queryBuilder
            ->orderBy('push_notification.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/push/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Show Push notification
     * @Route("/push/{id}", name="push_show")
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $id = $request->get('id');

        /** @var PushNotification $push */
        $push = $this->repository->find($id);

        return $this->render('/admin/push/show.html.twig', [
            'push' => $push
        ]);
    }
}
