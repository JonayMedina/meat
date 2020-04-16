<?php

namespace App\Controller\Admin;

use App\Entity\Segment;
use Psr\Log\LoggerInterface;
use App\Repository\SegmentRepository;
use Symfony\Component\Intl\Currencies;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class SegmentController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class SegmentController extends AbstractController
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
     * @var SegmentRepository
     */
    private $repository;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     */
    public function __construct(LoggerInterface $logger, PaginatorInterface $paginator, TranslatorInterface $translator, SegmentRepository $repository)
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->repository = $repository;
    }

    /**
     *
     * @Route("/segment", name="segments_index")
     * @param Request $request
     * @param CurrencyContextInterface $currencyContext
     * @return Response
     */
    public function indexAction(Request $request, CurrencyContextInterface $currencyContext)
    {
        $filter = $request->query->get('filter');
        $page = $request->query->getInt('page', 1);

        $queryBuilder = $this->repository
            ->createQueryBuilder('segment');

        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('segment.name LIKE :filter OR segment.minAge LIKE :filter OR segment.maxAge LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        $queryBuilder
            ->orderBy('segment.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/segment/index.html.twig', [
            'pagination' => $pagination,
            'total' => $this->countSegments(),
            'currency' => Currencies::getSymbol($currencyContext->getCurrencyCode())
        ]);
    }

    /**
     * Delete segment
     * @Route("/segment/{id}", name="segments_delete", methods={"DELETE"})
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $id = $request->get('id');

        $manager = $this->get('doctrine')->getManager();
        /** @var Segment $segment */
        $segment = $this->repository->find($id);

        $manager->remove($segment);

        try {
            $manager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @return int|mixed|string|null
     */
    private function countSegments()
    {
        try {
            $total = $this->repository
                ->createQueryBuilder('segment')
                ->select('COUNT(segment)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $total = null;
        }

        return $total;
    }
}
