<?php

namespace App\Controller\Admin;

use App\Entity\AboutStore;
use App\Form\Admin\AboutStoreType;
use App\Repository\AboutStoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AboutController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class AboutController extends AbstractController
{
    /**
     *
     * @Route("/about", name="about_index")
     * @param Request $request
     * @param AboutStoreRepository $repository
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function indexAction(Request $request, AboutStoreRepository $repository, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $aboutStore = $repository->findLatest();

        if (!$aboutStore instanceof AboutStore) {
            $aboutStore = new AboutStore();
        }

        $form = $this->createForm(AboutStoreType::class, $aboutStore);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($aboutStore);

            try {
                $entityManager->flush();

                $this->addFlash('success', $translator->trans('app.ui.about_store_success_message'));
            } catch (\Exception $exception) {
                $logger->error($exception->getMessage());
                $this->addFlash('error', $translator->trans('app.ui.about_store_error_while_saving_message'));
            }

            return $this->redirectToRoute('dashboard_index');
        }

        return $this->render('/admin/about/index.html.twig', [
            'about' => $aboutStore,
            'form' => $form->createView()
        ]);
    }
}
