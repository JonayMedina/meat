<?php

namespace App\Controller\Admin;


use App\Entity\PushNotification;
use App\Form\Admin\PushNotificationType;
use App\Message\PushNotificationMessage;
use Psr\Log\LoggerInterface;
use App\Repository\SegmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\PushNotificationRepository;
use Symfony\Component\Messenger\MessageBusInterface;
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
     * New Push notification
     * @Route("/push/new", name="push_new")
     * @param Request $request
     * @param MessageBusInterface $bus
     * @return Response
     */
    public function newAction(Request $request, MessageBusInterface $bus)
    {
        $push = new PushNotification();

        $form = $this->createForm(PushNotificationType::class, $push);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($push);

            try {
                $this->entityManager->flush();

                /***
                 * Queue push_notification entry.
                 */
                $bus->dispatch(new PushNotificationMessage($push));
                $this->addFlash('success', $this->translator->trans('app.ui.push_new_success_message'));

            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->translator->trans('app.ui.push_new_error_message'));
                $this->logger->error($exception->getMessage());
            }

            return $this->redirectToRoute('push_index');
        }

        return $this->render('/admin/push/new.html.twig', [
            'push' =>  $push,
            'form' => $form->createView(),
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
