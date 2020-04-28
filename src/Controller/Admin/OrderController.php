<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class OrderController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class OrderController extends AbstractController
{
    const PAGINATOR_LIMIT = 20;

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
     * CouponController constructor.
     * @param LoggerInterface $logger
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LoggerInterface $logger, PaginatorInterface $paginator, TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    /**
     *
     * @Route("/order", name="orders_index")
     * @param Request $request
     * @param CurrencyContextInterface $currencyContext
     * @return Response
     */
    public function indexAction(Request $request, CurrencyContextInterface $currencyContext)
    {
        $filter = $request->query->get('filter');
        $page = $request->query->getInt('page', 1);

        $queryBuilder = $this->entityManager->getRepository('App:Order\Order')
            ->createQueryBuilder('o');

        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('o.number LIKE :filter OR o.total LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        $queryBuilder
            ->orderBy('o.createdAt', 'DESC');

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/order/index.html.twig', [
            'pagination' => $pagination,
            'currency' => $currencyContext->getCurrencyCode(),
        ]);
    }
}
