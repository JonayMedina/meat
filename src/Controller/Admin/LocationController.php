<?php

namespace App\Controller\Admin;

use App\Entity\Location;
use Psr\Log\LoggerInterface;
use App\Service\UploaderHelper;
use App\Form\Admin\LocationType;
use App\Repository\LocationRepository;
use League\Flysystem\FileExistsException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @param UploaderHelper $uploaderHelper
     * @return Response
     * @throws FileExistsException
     */
    public function newAction(Request $request, UploaderHelper $uploaderHelper)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $location = new Location();
        $errors = [];

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST)) {
            $schedule = $request->get('schedule');
            $location->setSchedule($schedule);
            $errors = $this->validateExtraFields($schedule);
        }

        if ($form->isSubmitted() && $form->isValid() && count($errors) <= 0) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['photoType']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadLocationImage($uploadedFile, $location->getPhoto());
                $location->setPhoto($newFilename);
            }

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
            'errors' => $errors
        ]);
    }

    /**
     * Edit location.
     * @Route("/location/{id}/edit", name="locations_edit")
     * @param Request $request
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function editAction(Request $request, UploaderHelper $uploaderHelper)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $location = $this->repository->find($request->get('id'));
        $errors = [];

        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($request->isMethod(Request::METHOD_POST)) {
            $schedule = $request->get('schedule');
            $location->setSchedule($schedule);

            $errors = $this->validateExtraFields($schedule);
        }

        if ($form->isSubmitted() && $form->isValid() && count($errors) <= 0) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['photoType']->getData();
            if ($uploadedFile) {
                try {
                    $newFilename = $uploaderHelper->uploadLocationImage($uploadedFile, $location->getPhoto());
                    $location->setPhoto($newFilename);
                } catch (\Exception $exception) {
                    $this->addFlash('danger', $exception->getMessage());
                }
            }

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
            'errors' => $errors
        ]);
    }

    /**
     * Delete location
     * @Route("/location/{id}", name="locations_delete", methods={"DELETE"})
     * @param Request $request
     * @param UploaderHelper $uploaderHelper
     * @return Response
     */
    public function deleteAction(Request $request, UploaderHelper $uploaderHelper)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $location = $this->repository->find($request->get('id'));

        $existingFilename = $location->getPhoto();
        $entityManager->remove($location);

        try {
            $entityManager->flush();
            $uploaderHelper->deleteLocationImage($existingFilename);

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
     * @param $array
     * @return array
     */
    private function validateExtraFields($array) {
        $errors = [];

        if (!$this->validateArray($array['in_week']) || !$this->validateArray($array['weekend'])) {
            $errors['schedule'] = 'app.ui.admin.not_empty';
        }

        return $errors;
    }

    /**
     * @param $array
     * @return bool
     */
    private function validateArray($array) {
        if ($array['start'] == '' || $array['end'] == '') {
            return false;
        } else {
            return true;
        }
    }
}
