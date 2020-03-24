<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use App\Form\Admin\LocationType;
use App\Repository\LocationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LocationController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class LocationController extends AbstractController
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
     * @var LocationRepository
     */
    private $repository;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     * @param LocationRepository $repository
     */
    public function __construct(LoggerInterface $logger, PaginatorInterface $paginator, TranslatorInterface $translator, LocationRepository $repository)
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->repository = $repository;
    }

    /**
     *
     * @Route("/location", name="locations_index")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $filter = $request->query->get('filter');
        $page = $request->query->getInt('page', 1);

        $queryBuilder = $this->repository
            ->createQueryBuilder('location');

        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('location.name LIKE :filter OR location.address LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        $queryBuilder
            ->orderBy('location.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/location/index.html.twig', [
            'pagination' => $pagination,
            'total' => $this->countLocations()
        ]);
    }

    /**
     * New location.
     * @Route("/location/new", name="locations_new")
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $location = new Location();

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST)) {
            $location->setSchedule($request->get('schedule'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleUpload($form, $location);
            $entityManager->persist($location);

            try {
                $entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.ui.location_new_success_message'));

            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->translator->trans('app.ui.location_new_error_message'));
                $this->logger->error($exception->getMessage());
            }

            return $this->redirectToRoute('locations_index');
        }

        return $this->render('/admin/location/new.html.twig', [
            'location' =>  $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edit location.
     * @Route("/location/{id}/edit", name="locations_edit")
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $location = $this->repository->find($request->get('id'));

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST)) {
            $location->setSchedule($request->get('schedule'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleUpload($form, $location);

            try {
                $entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.ui.location_edit_success_message'));

            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->translator->trans('app.ui.location_edit_error_message'));
                $this->logger->error($exception->getMessage());
            }

            return $this->redirectToRoute('locations_index');
        }

        return $this->render('/admin/location/edit.html.twig', [
            'location' =>  $location,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete location
     * @Route("/location/{id}", name="locations_delete", methods={"DELETE"})
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $location = $this->repository->find($request->get('id'));

        $entityManager->remove($location);

        try {
            $entityManager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {

            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Return total of active coupons.
     * @return int|null
     */
    private function countLocations(): ?int
    {
        try {
            return $queryBuilder = $this->repository
                ->createQueryBuilder('location')
                ->select('COUNT(location)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }

    /**
     * Handle form upload.
     * @param FormInterface $form
     * @param Location $location
     */
    private function handleUpload(FormInterface $form, Location $location)
    {
        /** @var UploadedFile $locationFile */
        $locationFile = $form->get('photoType')->getData();

        if ($locationFile) {
            $originalFilename = pathinfo($locationFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$locationFile->guessExtension();

            // Move the file to the directory where brochures are stored
            $locationFile->move( $this->getParameter('location_directory'), $newFilename);

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            $location->setPhoto($newFilename);
        }
    }
}
