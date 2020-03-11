<?php

namespace App\Controller\Admin;

use App\Entity\AboutStore;
use App\Form\Admin\PurchaseTextsType;
use App\Repository\AboutStoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class SettingsController extends AbstractController
{
    /**
     *
     * @Route("/purchase/texts", name="purchase_texts")
     * @param Request $request
     * @param AboutStoreRepository $repository
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function textsAction(Request $request, AboutStoreRepository $repository, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $aboutStore = $repository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            $aboutStore = new AboutStore();
        }

        $form = $this->createForm(PurchaseTextsType::class, $aboutStore);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($aboutStore);

            try {
                $entityManager->flush();

                $this->addFlash('success', $translator->trans('app.ui.settings.purchase_texts_success_message'));
            } catch (\Exception $exception) {
                $logger->error($exception->getMessage());
                $this->addFlash('error', $translator->trans('app.ui.settings.purchase_texts_error_while_saving_message'));
            }

            return $this->redirectToRoute('dashboard_index');
        }

        return $this->render('/admin/configuration/texts.html.twig', [
            'about' => $aboutStore,
            'form' => $form->createView()
        ]);
    }

    /**
     *
     * @Route("/purchase/settings", name="purchase_settings")
     * @return Response
     */
    public function settingsAction()
    {
        return $this->render('/admin/configuration/settings.html.twig');
    }

    /**
     *
     * @Route("/holidays", name="holidays")
     * @return Response
     */
    public function holidaysAction()
    {
        return $this->render('/admin/configuration/holidays.html.twig');
    }

    /**
     * Show search box setting page.
     * @Route("/search-box", name="searcher")
     * @param AboutStoreRepository $repository
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function searcherAction(AboutStoreRepository $repository, EntityManagerInterface $entityManager)
    {
        $aboutStore = $repository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            $aboutStore = new AboutStore();
            $entityManager->persist($aboutStore);
            $entityManager->flush();
        }

        return $this->render('/admin/configuration/searcher.html.twig', [
            'about' => $aboutStore
        ]);
    }

    /**
     *
     * @Route("/caregory-color", name="category_color")
     * @return Response
     */
    public function categoryColorAction()
    {
        return $this->render('/admin/configuration/category_color.html.twig');
    }

    /**
     * Toggle status of search box setting.
     * @Route("/search-box/toggle", name="toggle_searcher", options={"expose" = "true"})
     *
     * @param AboutStoreRepository $repository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function toggleSearchBoxStatus(AboutStoreRepository $repository, EntityManagerInterface $entityManager)
    {
        $aboutStore = $repository->findLatest();

        $aboutStore->setShowProductSearchBox(!$aboutStore->isShowProductSearchBox());

        try {
            $entityManager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
