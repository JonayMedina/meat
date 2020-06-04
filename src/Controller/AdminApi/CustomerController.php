<?php

namespace App\Controller\AdminApi;

use App\Model\APIResponse;
use App\Service\OrderService;
use App\Entity\Customer\Customer;
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
}
