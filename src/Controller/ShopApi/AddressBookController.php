<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Core\Factory\AddressFactoryInterface;

/**
 * AddressBookController
 * @Route("/address-book")
 */
class AddressBookController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AddressFactoryInterface
     */
    private $addressFactory;

    /**
     * AddressBookController constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param AddressFactoryInterface $addressFactory
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, AddressFactoryInterface $addressFactory)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @Route(
     *     "/.{_format}",
     *     name="sylius_shop_api_address_book_index",
     *     methods={"GET"}
     * )
     *
     * @return Response
     */
    public function indexAction()
    {
        $list = [];

        /** @var Address[] $addresses */
        $addresses = $this->entityManager->getRepository('App:Addressing\Address')
            ->createQueryBuilder('a')
            ->andWhere('a.customer = :customer')
            ->setParameter('customer', $this->getCustomer())
            ->getQuery()
            ->getResult();

        foreach ($addresses as $address) {
            $list[] = $this->serializeAddress($address);
        }

        $statusCode = Response::HTTP_OK;
        $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Address Book', $list);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/.{_format}",
     *     name="sylius_shop_api_address_book_create",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $askFor = $request->get('ask_for');
        $fullAddress = $request->get('full_address');
        $phoneNumber = $request->get('phone_number');

        if (empty($askFor)) {
            return $this->renderError('Invalid name');
        }

        if (empty($fullAddress)) {
            return $this->renderError('Invalid address');
        }

        if (empty($phoneNumber)) {
            return $this->renderError('Invalid phone number');
        }

        /** @var Address $address */
        $address = $this->addressFactory->createNew();
        $address->setFirstName($askFor);
        $address->setFullAddress($fullAddress);
        $address->setPhoneNumber($phoneNumber);
        $address->setCustomer($this->getCustomer());

        $this->entityManager->persist($address);

        try {
            $this->entityManager->flush();

            $statusCode = Response::HTTP_CREATED;
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Created.', $this->serializeAddress($address));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);

        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return $this->renderError($exception->getMessage());
        }
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="sylius_shop_api_address_book_update",
     *     methods={"PUT"}
     * )
     * @param Request $request
     */
    public function editAction(Request $request)
    {
        $askFor = $request->get('ask_for');
        $fullAddress = $request->get('full_address');
        $phoneNumber = $request->get('phone_number');

        if (empty($askFor)) {
            return $this->renderError('Invalid name');
        }

        if (empty($fullAddress)) {
            return $this->renderError('Invalid address');
        }

        if (empty($phoneNumber)) {
            return $this->renderError('Invalid phone number');
        }

        $id = $request->get('id');
        $address = $this->entityManager->getRepository('App:Addressing\Address')->find($id);

        if (!$address instanceof Address) {
            return $this->renderError('Not found', Response::HTTP_NOT_FOUND);
        }

        if ($address->getCustomer() != $this->getCustomer()) {
            return $this->renderError('Not allowed', Response::HTTP_FORBIDDEN);
        }

        if (!empty($askFor)) {
            $address->setFirstName($askFor);
        }

        if (!empty($phoneNumber)) {
            $address->setPhoneNumber($phoneNumber);
        }

        if (!empty($fullAddress)) {
            $address->setFullAddress($fullAddress);
        }

        try {
            $this->entityManager->flush();

            $statusCode = Response::HTTP_OK;
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Updated.', $this->serializeAddress($address));
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return $this->renderError($exception->getMessage());
        }

    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="sylius_shop_api_address_book_delete",
     *     methods={"DELETE"}
     * )
     * @param Request $request
     */
    public function deleteAction(Request $request)
    {
        $id = $request->get('id');
        $address = $this->entityManager->getRepository('App:Addressing\Address')->find($id);

        if (!$address instanceof Address) {
            return $this->renderError('Not found', Response::HTTP_NOT_FOUND);
        }

        if ($address->getCustomer() != $this->getCustomer()) {
            return $this->renderError('Not allowed', Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($address);

        try {
            $this->entityManager->flush();

            $statusCode = Response::HTTP_NO_CONTENT;
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Deleted.', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return $this->renderError($exception->getMessage());
        }
    }

    /**
     * @return Customer
     */
    private function getCustomer(): Customer
    {
        /** @var ShopUser $user */
        $user = $this->getUser();

        /** @var Customer $customer */
        $customer = $user->getCustomer();

        return $customer;
    }

    /**
     * @param $message
     * @param int $statusCode
     * @return Response
     */
    private function renderError($message, $statusCode = Response::HTTP_BAD_REQUEST)
    {
        $statusCode = $statusCode ? $statusCode : Response::HTTP_BAD_REQUEST;

        $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $message, []);
        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @param Address $address
     * @return array
     */
    private function serializeAddress(Address $address): array
    {
        return [
            'id' => $address->getId(),
            'ask_for' => $address->getFirstName(),
            'full_address' => $address->getFullAddress(),
            'phone_number' => $address->getPhoneNumber(),
            'status' => $address->getStatus()
        ];
    }

}
