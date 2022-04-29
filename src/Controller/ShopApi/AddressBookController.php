<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use App\Service\OrderService;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Contracts\Translation\TranslatorInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Core\Factory\AddressFactoryInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * AddressBookController constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param AddressFactoryInterface $addressFactory
     * @param TranslatorInterface $translator
     * @param OrderService $orderService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        AddressFactoryInterface $addressFactory,
        TranslatorInterface $translator,
        OrderService $orderService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->addressFactory = $addressFactory;
        $this->translator = $translator;
        $this->orderService = $orderService;
    }

    /**
     * @Route(
     *     "/.{_format}",
     *     name="sylius_shop_api_address_book_index",
     *     methods={"GET"}
     * )
     *
     * @QueryParam(name="type", default="", nullable=true, description="Address type filter")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return Response
     */
    public function indexAction(ParamFetcherInterface $paramFetcher)
    {
        $list = [];
        $type = $paramFetcher->get('type');

        $addressesQuery = $this->entityManager->getRepository('App:Addressing\Address')
            ->createQueryBuilder('a')
            ->andWhere('a.customer = :customer')
            ->andWhere('a.type != :type')
            ->setParameter('customer', $this->getCustomer())
            ->setParameter('type', '');

        if (!empty($type)) {
            $addressesQuery
                ->andWhere('a.type = :filterType')
                ->setParameter('filterType', $type);
        }

        /** @var Address[] $addresses */
        $addresses = $addressesQuery
            ->getQuery()
            ->getResult();

        foreach ($addresses as $address) {
            $list[] = $this->orderService->serializeAddress($address);
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
        $type = $request->get('type');
        $taxId = $request->get('tax_id');
        $customer = $this->getCustomer();
        $isDefault = filter_var($request->get('is_default', false), FILTER_VALIDATE_BOOLEAN);
        $phoneNumber = $this->sanitizePhoneNumber($phoneNumber);

        if ($type == Address::TYPE_SHIPPING && $this->countAddressByType($type) >= ShopUser::SHIPPING_ADDRESS_LIMIT) {
            $message = $this->translator->trans('api.address_book.shipping_limit_reached', ['%limit%' => ShopUser::SHIPPING_ADDRESS_LIMIT]);

            throw new TooManyRequestsHttpException(null,$message);
        }

        if ($type == Address::TYPE_BILLING && $this->countAddressByType($type) >= ShopUser::BILLING_ADDRESS_LIMIT) {
            if (ShopUser::BILLING_ADDRESS_LIMIT == 1) {
                $message = $this->translator->trans('api.address_book.billing_one_address_limit_reached');
            } else {
                $message = $this->translator->trans('api.address_book.billing_limit_reached', ['%limit%' => ShopUser::BILLING_ADDRESS_LIMIT]);
            }

            throw new TooManyRequestsHttpException(null,$message);
        }

        if (empty($askFor)) {
            return $this->renderError('Invalid name');
        }

        if (empty($fullAddress)) {
            return $this->renderError('Invalid address');
        }

        if (empty($phoneNumber) || !$this->isPhoneNumberValid($phoneNumber)) {
            return $this->renderError('Invalid phone number');
        }

        if (empty($type)) {
            return $this->renderError('Invalid address type');
        }

        if (empty($taxId) && $type == Address::TYPE_BILLING) {
            return $this->renderError('Invalid tax id');
        }

        if ($type == Address::TYPE_SHIPPING && !empty($taxId)) {
            $taxId = null;
        }

        /** @var Address $address */
        $address = $this->addressFactory->createNew();
        $address->setAnnotations($askFor);
        $address->setFullAddress($fullAddress);
        $address->setPhoneNumber($phoneNumber);
        $address->setCustomer($customer);
        $address->setType($type);

        if (!empty($taxId)) {
            $address->setTaxId($taxId);
        }

        $this->entityManager->persist($address);

        try {
            if ($address->getType() == Address::TYPE_BILLING) {
                /** Set default billing address of customer */
                if (!$customer->getDefaultBillingAddress()) {
                    $customer->setDefaultBillingAddress($address);
                }
            } else {
                if ($isDefault) {
                    $customer->setDefaultAddress($address);
                }
            }

            $this->entityManager->flush();

            $statusCode = Response::HTTP_CREATED;
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Created.', $this->orderService->serializeAddress($address));
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
        $taxId = $request->get('tax_id');

        $customer = $this->getCustomer();
        $isDefault = filter_var($request->get('is_default', false), FILTER_VALIDATE_BOOLEAN);

        $id = $request->get('id');
        $address = $this->entityManager->getRepository('App:Addressing\Address')->find($id);

        if (empty($askFor)) {
            return $this->renderError('Invalid name');
        }

        if (empty($fullAddress)) {
            return $this->renderError('Invalid address');
        }

        if (empty($phoneNumber)) {
            return $this->renderError('Invalid phone number');
        }

        if (empty($taxId) && $address->getType() == Address::TYPE_BILLING) {
            return $this->renderError('Invalid tax id');
        }

        if ($address->getType() == Address::TYPE_SHIPPING && !empty($taxId)) {
            $taxId = null;
        }

        if (!$address instanceof Address) {
            return $this->renderError('Not found', Response::HTTP_NOT_FOUND);
        }

        if ($address->getCustomer() != $this->getCustomer()) {
            return $this->renderError('Not allowed', Response::HTTP_FORBIDDEN);
        }

        if (!empty($askFor)) {
            $address->setAnnotations($askFor);
        }

        if (!empty($phoneNumber)) {
            $address->setPhoneNumber($phoneNumber);
        }

        if (!empty($fullAddress)) {
            $address->setFullAddress($fullAddress);
        }

        if (!empty($taxId)) {
            $address->setTaxId($taxId);
        }

        try {
            if ($isDefault) {
                $customer->setDefaultAddress($address);
            }

            $this->entityManager->flush();

            $statusCode = Response::HTTP_OK;
            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Updated.', $this->orderService->serializeAddress($address));
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

        foreach ($address->getChildren() as $child) {
            /** @var Address $child */
            $child->setParent(NULL);
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
     * @param $type
     * @return int|null
     */
    private function countAddressByType($type): ?int
    {
        return $this->entityManager->getRepository('App:Addressing\Address')
            ->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.customer = :customer')
            ->andWhere('a.type = :type')
            ->setParameter('customer', $this->getCustomer())
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param $phoneNumber
     * @return bool
     */
    private function isPhoneNumberValid($phoneNumber): bool
    {
        return is_numeric($phoneNumber) && (strlen(trim($phoneNumber)) ==  8);
    }

    /**
     * @param $phoneNumber
     * @return string
     */
    private function sanitizePhoneNumber($phoneNumber)
    {
        return str_replace("-", "", $phoneNumber);
    }
}
