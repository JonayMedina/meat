<?php

namespace App\Controller\Admin;

use App\Entity\FAQ;
use App\Repository\FAQRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $entityManager
     * @param FAQRepository $repository
     */
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, FAQRepository $repository)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
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
}
