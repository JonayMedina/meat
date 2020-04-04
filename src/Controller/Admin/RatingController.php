<?php

namespace App\Controller\Admin;

use App\Service\DashboardService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class RatingController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class RatingController extends AbstractController
{
    const PAGINATOR_LIMIT = 10;

    const ORDER_RECENT = 'recent';

    const ORDER_ASC = 'asc';

    const ORDER_DESC = 'desc';

    /**
     *
     * @Route("/rating", name="ratings_index", options={"expose"="true"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CurrencyContextInterface $currency
     * @param PaginatorInterface $paginator
     * @param DashboardService $dashboardService
     * @return Response
     */
    public function indexAction(Request $request, EntityManagerInterface $entityManager, CurrencyContextInterface $currency, PaginatorInterface $paginator, DashboardService $dashboardService)
    {
        $page = $request->get('page', 1);
        $filter = $request->get('filter');
        $starsFilter = $request->get('stars');
        $orderFilter = $request->get('order');

        $queryBuilder = $entityManager->getRepository('App:Order\Order')
            ->createQueryBuilder('o')
            ->leftJoin('o.customer', 'customer')
            ->andWhere('o.checkoutState = :checkoutState')
            ->setParameter('checkoutState', OrderCheckoutStates::STATE_COMPLETED);

        /**
         * Search filter...
         */
        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('o.number LIKE :filter OR o.ratingComment LIKE :filter OR customer.firstName LIKE :filter OR customer.lastName LIKE :filter OR customer.email LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        $queryBuilder
            ->andWhere('o.rating IS NOT NULL')
            ->orderBy('o.createdAt', 'DESC');

        /**
         * Filter by stars...
         */
        if (!empty($starsFilter)) {
            switch ($starsFilter) {
                case 'comments':
                    $queryBuilder
                        ->andWhere('o.ratingComment IS NOT NULL');
                    break;

                default:
                    $queryBuilder
                        ->andWhere('o.rating = :rating')
                        ->setParameter('rating', $starsFilter);

                    break;
            }
        }

        /**
         * Order...
         */
        if (!empty($orderFilter)) {
            switch ($orderFilter) {
                case self::ORDER_RECENT:
                    $queryBuilder
                        ->orderBy('o.createdAt', 'DESC');

                    break;
                case self::ORDER_ASC:
                    $queryBuilder
                        ->orderBy('o.rating', 'ASC');
                    break;
                case self::ORDER_DESC:
                    $queryBuilder
                        ->orderBy('o.rating', 'DESC');
                    break;
            }
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/rating/index.html.twig', [
            'pagination' => $pagination,
            'currency' => $currency,
            'dashboard' => $dashboardService
                ->calculateAverageRating()
        ]);
    }
}
