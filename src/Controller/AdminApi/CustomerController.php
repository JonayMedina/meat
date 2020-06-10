<?php

namespace App\Controller\AdminApi;

use App\Entity\Locale\Locale;
use App\Model\APIResponse;
use App\Pagination\PaginationFactory;
use App\Service\OrderService;
use App\Entity\Customer\Customer;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository;

/**
 * CustomerController
 * @Route("/customers")
 */
class CustomerController extends AbstractFOSRestController
{
    const ITEMS_PER_PAGE = 10;

    /** @var CustomerRepository */
    private $customerRepository;

    /** @var OrderService */
    private $orderService;

    /**
     * CustomerController constructor.
     * @param CustomerRepository $customerRepository
     * @param OrderService $orderService
     */
    public function __construct(
        CustomerRepository $customerRepository,
        OrderService $orderService
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="admin_api_customers_index",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @param PaginationFactory $paginationFactory
     * @return Response
     */
    public function indexAction(Request $request, PaginationFactory $paginationFactory): Response
    {
        $statusCode = Response::HTTP_OK;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', self::ITEMS_PER_PAGE);
        $search = $request->query->get('search');

        $queryBuilder = $this->getQueryBuilder($search);
        $paginatedCollection = $paginationFactory->createCollection($queryBuilder, $search, $page, $limit, 'admin_api_customers_index', [], 'Customer list.', $statusCode, 'info');

        $list = [];
        foreach ($paginatedCollection->recordset as $customer) {
            $list[] = $this->orderService->serializeCustomer($customer);
        }
        $paginatedCollection->recordset = $list;

        $view = $this->view($paginatedCollection, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="admin_api_customers_show",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $customer = $this->getCustomer($request);

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeCustomer($customer)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Customer|null
     */
    private function getCustomer(Request $request): ?Customer
    {
        $id = $request->get('id');

        /** @var Customer $customer */
        $customer = $this->customerRepository->find($id);

        return $customer;
    }

    /**
     * @param $search
     * @return QueryBuilder
     */
    private function getQueryBuilder($search): QueryBuilder
    {
        $queryBuilder = $this->customerRepository
            ->createQueryBuilder('customer')
            ->orderBy('customer.firstName', 'ASC');

        if (!empty($search)) {
            $queryBuilder
                ->andWhere('customer.firstName LIKE :search OR customer.lastName LIKE :search OR customer.email LIKE :search OR customer.phoneNumber LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $queryBuilder;
    }
}
