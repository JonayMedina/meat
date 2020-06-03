<?php

namespace App\Controller\AdminApi;

use App\Model\APIResponse;
use App\Service\AddressService;
use App\Service\OrderService;
use App\Entity\Addressing\Address;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\AddressRepository;

/**
 * AddressController
 * @Route("/addresses")
 */
class AddressController extends AbstractFOSRestController
{
    /** @var OrderService $orderService */
    private $orderService;

    /** @var AddressRepository $addressRepository */
    private $addressRepository;

    /**
     * @var AddressService
     */
    private $addressService;

    /**
     * AddressController constructor.
     * @param OrderService $orderService
     * @param AddressRepository $addressRepository
     * @param AddressService $addressService
     */
    public function __construct(
        OrderService $orderService,
        AddressRepository $addressRepository,
        AddressService $addressService
    ) {
        $this->orderService = $orderService;
        $this->addressRepository = $addressRepository;
        $this->addressService = $addressService;
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="admin_api_address_show",
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $address = $this->getAddress($request);

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeAddress($address, true)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="admin_api_address_validate",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function validateAction(Request $request)
    {
        $address = $this->getAddress($request);
        $this->addressService->validate($address);

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeAddress($address, true)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="admin_api_address_reject",
     *     methods={"DELETE"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function rejectAction(Request $request)
    {
        $address = $this->getAddress($request);
        $this->addressService->reject($address);

        $statusCode = Response::HTTP_OK;
        $data = new APIResponse(
            $statusCode,
            APIResponse::TYPE_INFO,
            'Ok',
            $this->orderService->serializeAddress($address, true)
        );

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @return Address
     */
    private function getAddress(Request $request): Address
    {
        $id = $request->get('id');

        /** @var Address $address */
        $address = $this->addressRepository->find($id);

        return $address;
    }
}
