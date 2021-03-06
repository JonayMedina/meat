<?php

namespace App\Controller\Admin;

use App\Entity\FAQ;
use Doctrine\ORM\Query;
use App\Form\Admin\FAQType;
use Psr\Log\LoggerInterface;
use App\Repository\FAQRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class FAQController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class FAQController extends AbstractController
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FAQRepository
     */
    private $repository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param FAQRepository $repository
     * @param TranslatorInterface $translator
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, FAQRepository $repository, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * FAQ Index
     * @Route("/faq", name="faqs_index")
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $filter = $request->query->get('filter');

        $queryBuilder = $this->repository
            ->createQueryBuilder('faq');

        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('faq.question LIKE :filter OR faq.answer LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        $queryBuilder
            ->orderBy('faq.position', 'ASC');

        $faqs = $queryBuilder
            ->getQuery()
            ->getResult();

        return $this->render('/admin/faq/index.html.twig', [
            'faqs' => $faqs,
            'total' => $this->countFAQs(),
        ]);
    }

    /**
     * Reorder FAQs
     * @Route("/faq/reorder", name="faqs_reorder", options={"expose"="true"}, methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function sortAction(Request $request)
    {
        $order = $request->get('order');
        $position = 1;

        foreach ($order as $id) {
            /** @var FAQ $faq */
            $faq = $this->repository->find($id);

            $faq->setPosition($position);
            $position++;
        }

        try {
            $this->entityManager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok']);
        } catch (\Exception $exception) {

            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

    }

    /**
     * New FAQ.
     * @Route("/faq/new", name="faqs_new")
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $type = $request->get('type');
        $errors['order'] = '';
        $errors['delivery'] = '';

        $faq = new FAQ();
        $faq->setPosition($this->guessNextPosition());

        if ($type == FAQ::TYPE_SCHEDULE) {
            $faq->setType(FAQ::TYPE_SCHEDULE);
        }

        $form = $this->createForm(FAQType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** Only for Schedule type. */
            if ($faq->getType() == FAQ::TYPE_SCHEDULE && $request->isMethod(Request::METHOD_POST)) {
                $order = $request->get('order');
                $delivery = $request->get('delivery');

                $errors['order'] = $this->validateExtraFields($order, 'order');
                $errors['delivery'] = $this->validateExtraFields($delivery, 'delivery');

                $faq->setOrderDeliveryTime($delivery);
                $faq->setTimeToPlaceAnOrder($order);
            }

            if ($errors['order'] == '' && $errors['delivery'] == '') {
                $entityManager->persist($faq);

                try {
                    $entityManager->flush();
                    $this->addFlash('success', $this->translator->trans('app.ui.faq_new_success_message'));

                } catch (\Exception $exception) {
                    $this->addFlash('danger', $this->translator->trans('app.ui.faq_new_error_message'));
                    $this->logger->error($exception->getMessage());
                }

                return $this->redirectToRoute('faqs_index');
            }
        }

        return $this->render('/admin/faq/new.html.twig', [
            'faq' =>  $faq,
            'form' => $form->createView(),
            'errors' => $errors
        ]);
    }

    /**
     * New FAQ.
     * @Route("/faq/{id}/edit", name="faqs_edit")
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $faq = $this->repository->find($request->get('id'));
        $errors['order'] = '';
        $errors['delivery'] = '';

        $form = $this->createForm(FAQType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** Only for Schedule type. */
            if ($faq->getType() == FAQ::TYPE_SCHEDULE && $request->isMethod(Request::METHOD_POST)) {
                $order = $request->get('order');
                $delivery = $request->get('delivery');

                $errors['order'] = $this->validateExtraFields($order, 'order');
                $errors['delivery'] = $this->validateExtraFields($delivery, 'delivery');

                $faq->setOrderDeliveryTime($delivery);
                $faq->setTimeToPlaceAnOrder($order);
            }

            if ($errors['order'] == '' && $errors['delivery'] == '') {
                try {
                    $entityManager->flush();
                    $this->addFlash('success', $this->translator->trans('app.ui.faq_edit_success_message'));

                } catch (\Exception $exception) {
                    $this->addFlash('danger', $this->translator->trans('app.ui.faq_edit_error_message'));
                    $this->logger->error($exception->getMessage());
                }

                return $this->redirectToRoute('faqs_index');
            }
        }

        return $this->render('/admin/faq/edit.html.twig', [
            'faq' =>  $faq,
            'form' => $form->createView(),
            'errors' => $errors
        ]);
    }

    /**
     * Delete FAQ
     * @Route("/faq/{id}", name="faqs_delete", methods={"DELETE"})
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $faq = $this->repository->find($request->get('id'));

        $entityManager->remove($faq);

        try {
            $entityManager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {

            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Return FAQs number.
     * @return mixed|null
     */
    private function countFAQs()
    {
        try {
            return $queryBuilder = $this->repository
                ->createQueryBuilder('faq')
                ->select('COUNT(faq)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }

    /**
     * Guess next position.
     * @return mixed|null
     */
    private function guessNextPosition(): ?int
    {
        try {
            $lastFaq = $this->repository
                ->createQueryBuilder('faq')
                ->select('faq.position')
                ->setMaxResults(1)
                ->orderBy('faq.position', 'DESC')
                ->getQuery()
                ->getSingleResult(Query::HYDRATE_ARRAY);

            if (isset($lastFaq['position'])) {
                return $lastFaq['position'] + 1;
            } else {
                return 1;
            }

        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage());

            return 1;
        }
    }

    /**
     * @param $array
     * @param $type
     * @return string
     */
    private function validateExtraFields($array, $type) {
        $errors = '';

        if (!$this->validateArray($array[0], $type) && !$this->validateArray($array[1], $type) && !$this->validateArray($array[2], $type)) {
            $errors = 'app.ui.admin.not_empty';
        }

        return $errors;
    }

    /**
     * @param $array
     * @param $type
     * @return bool
     */
    private function validateArray($array, $type) {
        if ($type == 'order') {
            if ($array['start'] == '' || $array['end'] == '') {
                return false;
            } else {
                return true;
            }
        } else {
            if ($array['name'] == '' || $array['start'] == '' || $array['end'] == '') {
                return false;
            } else {
                return true;
            }
        }
    }
}
