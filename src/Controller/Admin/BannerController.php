<?php

namespace App\Controller\Admin;

use App\Service\UploaderHelper;
use Psr\Log\LoggerInterface;
use App\Entity\PromotionBanner;
use App\Form\Admin\PromotionBannerType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PromotionBannerRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class BannerController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class BannerController extends AbstractController
{
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PromotionBannerRepository
     */
    private $repository;

    protected $imageMap = [
        'photoWebType' => 'PhotoWeb',
        'photoTabletType' => 'PhotoTablet',
        'photoMobileType' => 'PhotoMobile',
        'photoAppType' => 'PhotoApp',
    ];

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LoggerInterface $logger, PaginatorInterface $paginator, TranslatorInterface $translator, EntityManagerInterface $entityManager, PromotionBannerRepository $repository)
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    /**
     *
     * @Route("/banner", name="banners_index", methods={"GET"})
     * @return Response
     */
    public function indexAction()
    {
        $banners = $this->repository->findAll();

        return $this->render('/admin/banner/index.html.twig', [
            'banners' => $banners
        ]);
    }

    /**
     *
     * @Route("/banner/new", name="banners_new", methods={"GET", "POST"})
     * @param Request $request
     * @param UploaderHelper $uploaderHelper
     * @return Response
     * @throws \League\Flysystem\FileExistsException
     */
    public function newAction(Request $request, UploaderHelper $uploaderHelper)
    {
        $bannerCounter = $this->countBanners();

        if (null === $bannerCounter || $bannerCounter >= PromotionBanner::MAX_ITEMS) {
            return $this->render('/admin/banner/_error.html.twig');
        }

        $banner = new PromotionBanner();

        $form = $this->createForm(PromotionBannerType::class, $banner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($banner);
            $this->uploadImages($form, $banner, $uploaderHelper);

            try {
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.ui.banner_new_success_message'));
            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->translator->trans('app.ui.banner_new_error_message'));
                $this->logger->error($exception->getMessage());
            }

            return $this->redirectToRoute('banners_index');
        }

        return $this->render('/admin/banner/new.html.twig', [
            'banner' => $banner,
            'form' => $form->createView(),
        ]);
    }

    /**
     *
     * @Route("/banner/{id}", name="banners_edit", methods={"GET", "POST"})
     * @param Request $request
     * @param UploaderHelper $uploaderHelper
     * @return Response
     * @throws \League\Flysystem\FileExistsException
     */
    public function editAction(Request $request, UploaderHelper $uploaderHelper)
    {
        $id = $request->get('id');
        /** @var PromotionBanner $banner */
        $banner = $this->repository->find($id);

        $form = $this->createForm(PromotionBannerType::class, $banner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->uploadImages($form, $banner, $uploaderHelper);

            try {
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.ui.banner_edit_success_message'));
            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->translator->trans('app.ui.banner_edit_error_message'));
                $this->logger->error($exception->getMessage());
            }

            return $this->redirectToRoute('banners_index');
        }

        return $this->render('/admin/banner/edit.html.twig', [
            'banner' => $banner,
            'form' => $form->createView(),
        ]);
    }

    /**
     *
     * @Route("/banner/{id}", name="banners_delete", methods={"DELETE"})
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $id = $request->get('id');
        /** @var PromotionBanner $banner */
        $banner = $this->repository->find($id);

        $this->entityManager->remove($banner);

        try {
            $this->entityManager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Manage uploads.
     * @param FormInterface $form
     * @param PromotionBanner $banner
     * @param UploaderHelper $uploaderHelper
     * @throws \League\Flysystem\FileExistsException
     */
    private function uploadImages(FormInterface $form, PromotionBanner $banner, UploaderHelper $uploaderHelper)
    {
        foreach ($this->imageMap as $key => $method) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form[$key]->getData();

            $getter = 'get' . $method;
            $setter = 'set' . $method;

            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadBannerImage($uploadedFile, $banner->$getter());
                $banner->$setter($newFilename);
            }
        }
    }

    /**
     * @return int|null
     */
    private function countBanners(): ?int
    {
        $queryBuilder = $this->repository
            ->createQueryBuilder('banner')
            ->select('COUNT(banner.id)');

        try {
            return $queryBuilder
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            return null;
        }
    }
}
