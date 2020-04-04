<?php

namespace App\Controller\Admin;

use App\Entity\User\AdminUser;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class UserController extends AbstractController
{
    const PAGINATOR_LIMIT = 10;

    const ORDER_ABC = 'abc';

    const ORDER_RECENT = 'recent';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UserController constructor.
     * @param EntityManagerInterface $entityManager
     * @param PaginatorInterface $paginator
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, PaginatorInterface $paginator, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        $this->logger = $logger;
    }


    /**
     *
     * @Route("/user", name="users_index", options={"expose"="true"})
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AdminUser::ROLE_ADMIN);

        $page = $request->get('page', 1);
        $filter = $request->get('filter');
        $roleFilter = $request->get('role');
        $orderFilter = $request->get('order');

        $queryBuilder = $this->entityManager->getRepository('App:User\AdminUser')
            ->createQueryBuilder('a');

        /**
         * Search filter...
         */
        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('a.firstName LIKE :filter OR a.lastName LIKE :filter OR a.username LIKE :filter OR a.email LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        /**
         * Filter by roles...
         */
        if (!empty($roleFilter)) {
            $queryBuilder
                ->andWhere('a.roles LIKE :role')
                ->setParameter('role', '%'. $roleFilter .'%');
        }

        /**
         * Order...
         */
        if (!empty($orderFilter)) {
            switch ($orderFilter) {
                case self::ORDER_RECENT:
                    $queryBuilder
                        ->orderBy('a.createdAt', 'DESC');

                    break;
                case self::ORDER_ABC:
                    $queryBuilder
                        ->orderBy('a.firstName', 'ASC');
                    break;
            }
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/user/index.html.twig', [
            'pagination' => $pagination,
            'total' => $this->countUsers()
        ]);
    }

    private function countUsers()
    {
        try {
            $total = $this->entityManager->getRepository('App:User\AdminUser')
                ->createQueryBuilder('a')
                ->select('COUNT(a)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $total = null;
        }

        return $total;
    }
}
