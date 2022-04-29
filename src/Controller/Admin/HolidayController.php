<?php

namespace App\Controller\Admin;

use App\Entity\Holiday;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use App\Repository\HolidayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class HolidayController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class HolidayController extends AbstractController
{
    const PAGINATOR_LIMIT = 12;

    const ORDER_ASC = 'abc';

    const ORDER_CHRONOLOGICAL = 'chronological';

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var HolidayRepository
     */
    private $repository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param HolidayRepository $repository
     * @param TranslatorInterface $translator
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, HolidayRepository $repository, TranslatorInterface $translator, PaginatorInterface $paginator)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->translator = $translator;
        $this->paginator = $paginator;
    }

    /**
     *
     * @Route("/holidays", name="holidays", options={"expose"="true"})
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $filter = $request->query->get('filter');
        $month = $request->query->get('month');
        $order = $request->query->get('order');

        $page = $request->query->getInt('page', 1);

        $queryBuilder = $this->repository
            ->createQueryBuilder('holiday');

        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('holiday.name LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        if (!empty($month)) {
            $queryBuilder
                ->andWhere('holiday.monthName = :monthName')
                ->setParameter('monthName', $month);
        }

        if ($order === self::ORDER_CHRONOLOGICAL) {
            $queryBuilder
                ->orderBy('holiday.date', 'ASC');
        } else {
            $queryBuilder
                ->orderBy('holiday.name', 'ASC');
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/holiday/index.html.twig', [
            'pagination' => $pagination,
            'total' => $this->countHolidays(),
        ]);
    }

    /**
     * Create holiday
     * @Route("/holidays/new", name="holidays_new", options={"expose"="true"}, methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function newAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $name = $request->get('name');
        $date = $request->get('date');

        /** @var Holiday $holiday */
        $holiday = new Holiday();
        $holiday->setName($name);
        $holiday->setDate(new \DateTime($date));

        $entityManager->persist($holiday);

        try {
            $this->updateMonthName($holiday);
            $entityManager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok', 'recordset' => [
                'id' => $holiday->getId(),
                'name' => $holiday->getName(),
                'date' => $holiday->getDate()->format('Y-m-d'),
            ]], Response::HTTP_OK);
        } catch (\Exception $exception) {

            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Calendar view
     * @Route("/holidays/calendar", name="holidays_calendar")
     * @return Response
     */
    public function calendarAction()
    {
        return $this->render('/admin/holiday/calendar.html.twig');
    }

    /**
     * Update event
     * @Route("/holidays/update/{id}", name="holidays_update_event", options={"expose"="true"}, methods={"PUT"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function updateEvent(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $holiday = $this->repository->find($request->get('id'));
        $diff = ($request->get('days') > 0 ) ? ('+' . $request->get('days')) : $request->get('days');

        $newDate = new \DateTime( $holiday->getDate()->format('Y-m-d H:i:s') );
        $newDate->modify($diff . ' days');

        $holiday->setDate($newDate);
        $this->updateMonthName($holiday);

        try {
            $entityManager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {

            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Delete Holiday
     * @Route("/holidays/{id}", name="holidays_delete", methods={"DELETE"})
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $holiday = $this->repository->find($request->get('id'));

        $entityManager->remove($holiday);

        try {
            $entityManager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {

            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Return holiday collection.
     * @Route("/holidays/_async", name="holidays_ajax_index", options={"expose"="true"}, methods={"GET"})
     * @return Response
     */
    public function getHolidaysAction()
    {
        $data = [];

        /** @var Holiday[] $holidays */
        $holidays = $this->repository
            ->createQueryBuilder('holiday')
            ->orderBy('holiday.date', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($holidays as $holiday) {
            $data[] = [
                'id' => $holiday->getId(),
                'title' => $holiday->getName(),
                'start' => $holiday->getDate()->format('Y-m-d'),
                'allDay' => true
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * Return Holiday number.
     * @return mixed|null
     */
    private function countHolidays()
    {
        try {
            return $queryBuilder = $this->repository
                ->createQueryBuilder('holiday')
                ->select('COUNT(holiday)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }

    /**
     * @param Holiday|null $holiday
     */
    private function updateMonthName(?Holiday $holiday)
    {
        setlocale(LC_ALL,"es_ES");
        $string = $holiday->getDate()->format('Y-m-d');
        $date = \DateTime::createFromFormat("Y-m-d", $string);


        $monthName = ucfirst(strftime("%B",$date->getTimestamp()));
        $holiday->setMonthName($monthName);
    }
}
