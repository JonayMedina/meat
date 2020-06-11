<?php

namespace App\Controller\Admin;

use Psr\Log\LoggerInterface;
use App\Entity\TermsAndConditions;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Admin\TermsAndConditionsType;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TermsAndConditionsRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class TermsAndConditionsController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class TermsAndConditionsController extends AbstractController
{
    /**
     *
     * @Route("/terms", name="terms_index")
     * @param Request $request
     * @param TermsAndConditionsRepository $repository
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @return Response
     * @throws NonUniqueResultException
     */
    public function indexAction(Request $request, TermsAndConditionsRepository $repository, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger)
    {
        $terms = $repository->findLatest();

        if (!$terms instanceof TermsAndConditions) {
            $terms = new TermsAndConditions();
        }

        $form = $this->createForm(TermsAndConditionsType::class, $terms);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($terms);

            try {
                $entityManager->flush();

                // TODO: Send email to all registered users.
                $this->addFlash('success', $translator->trans('app.ui.terms_and_conditions_success_message'));
            } catch (\Exception $exception) {
                $logger->error($exception->getMessage());
                $this->addFlash('error', $translator->trans('app.ui.terms_and_conditions_error_while_saving_message'));
            }

            return $this->redirectToRoute('terms_index');
        }

        return $this->render('/admin/terms/index.html.twig', [
            'terms' => $terms,
            'form' => $form->createView()
        ]);
    }
}
