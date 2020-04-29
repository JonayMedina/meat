<?php

namespace App\Controller\Admin;

use App\Entity\Order\Order;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Shipping\Model\ShipmentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * Order index
     * @Route("/order", name="orders_index", options={"expose" = "true"})
     * @param Request $request
     * @param CurrencyContextInterface $currencyContext
     * @return Response
     */
    public function indexAction(Request $request, CurrencyContextInterface $currencyContext)
    {
        $filter = $request->query->get('filter');
        $status = $request->query->get('status');
        $sort = $request->query->get('order');
        $page = $request->query->getInt('page', 1);

        $queryBuilder = $this->entityManager->getRepository('App:Order\Order')
            ->createQueryBuilder('o');
            //->andWhere('o.state != :cartState')
            //->setParameter('cartState', OrderInterface::STATE_CART);

        /** Text search filter */
        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('o.number LIKE :filter OR o.total LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        /** Status filter */
        if (!empty($status)) {
            if ($status == Order::STATUS_PENDING) {
                $queryBuilder
                    ->andWhere('o.state = :state')
                    ->setParameter('state', OrderInterface::STATE_NEW);
            }

            if ($status == Order::STATUS_DELIVERED) {
                $queryBuilder
                    ->andWhere('o.shippingState = :shippingState')
                    ->setParameter('shippingState', ShipmentInterface::STATE_SHIPPED);
            }

            if ($status == Order::STATUS_CANCELLED) {
                $queryBuilder
                    ->andWhere('o.state = :state')
                    ->setParameter('state', OrderInterface::STATE_CANCELLED);
            }
        }

        /** Sort filter */
        if (!empty($sort)) {
            if ($sort == Order::SORT_ORDER_NUMBER) {
                $queryBuilder
                    ->orderBy('o.number', 'DESC');
            } else if ($sort == Order::SORT_RECENT) {
                $queryBuilder
                    ->orderBy('o.createdAt', 'DESC');
            } else {
                $queryBuilder
                    ->orderBy('o.createdAt', 'DESC');
            }
        } else {
            $queryBuilder
                ->orderBy('o.createdAt', 'DESC');
        }

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

    /**
     * @param Request $request
     * @Route("/order/{id}", name="orders_show", options={"expose" = "true"})
     */
    public function showAction(Request $request)
    {
        $id = $request->get('id');
        $order = $this->entityManager->getRepository('App:Order\Order')->find($id);

        return $this->render('/admin/order/show.html.twig', [
            'order' => $order
        ]);
    }
}
