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
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class TermsAndConditionsController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class TermsAndConditionsController extends AbstractController
{
    /**
     * @var TermsAndConditionsRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * TermsAndConditionsController constructor.
     * @param TermsAndConditionsRepository $repository
     * @param EntityManagerInterface $entityManager
     * @param TranslatorInterface $translator
     * @param LoggerInterface $logger
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        TermsAndConditionsRepository $repository,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        CustomerRepository  $customerRepository
    ) {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
    }


    /**
     *
     * @Route("/terms", name="terms_index")
     * @param Request $request
     * @return Response
     * @throws NonUniqueResultException
     */
    public function indexAction(Request $request)
    {
        $terms = $this->repository->findLatest();

        if (!$terms instanceof TermsAndConditions) {
            $terms = new TermsAndConditions();
        }

        $form = $this->createForm(TermsAndConditionsType::class, $terms);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($terms);

            try {
                $this->entityManager->flush();
                $this->sendNotificationToCustomers($terms);

                $this->addFlash('success', $this->translator->trans('app.ui.terms_and_conditions_success_message'));
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                $this->addFlash('error', $this->translator->trans('app.ui.terms_and_conditions_error_while_saving_message'));
            }

            return $this->redirectToRoute('terms_index');
        }

        return $this->render('/admin/terms/index.html.twig', [
            'terms' => $terms,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param TermsAndConditions|null $terms
     */
    private function sendNotificationToCustomers(TermsAndConditions $terms)
    {
        $customers = $this->customerRepository->findAll();

        foreach ($customers as $customer) {
            // TODO: Send email to all registered users.
        }
    }
}
