<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use App\Entity\Customer\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * RegisterController
 * @Route("/register")
 */
class RegisterController extends AbstractFOSRestController
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
     * RegisterController constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    /**
     * Complete register
     * @Route("/complete-register", name="shop_api_complete_register", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function completeAction(Request $request)
    {
        $email = $request->get('email');
        $gender = $request->get('gender') ? $request->get('gender') : 'u';
        $birthdate = $request->get('birthdate');

        $customer = $this->getCustomer($email);

        if (!$customer instanceof Customer) {
            $statusCode = Response::HTTP_NOT_FOUND;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Customer not found.', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        if (!$this->validateGender($gender)) {
            $statusCode = Response::HTTP_BAD_REQUEST;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Invalid gender format', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        if (!$this->validateDate($birthdate)) {
            $statusCode = Response::HTTP_BAD_REQUEST;

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Invalid date format', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $datetime = new \DateTime();
        $newDate = $datetime->createFromFormat('Y-m-d H:i:s', $birthdate . ' 00:00:00');

        $customer->setGender($gender);
        $customer->setBirthday($newDate);

        /** @var ShopUser $shopUser */
        $shopUser = $customer->getUser();

        if (null == $shopUser->getTermsAndConditionsAcceptedAt()) {
            $shopUser->setTermsAndConditionsAcceptedAt(new \DateTime());
        }

        try {
            $this->entityManager->flush();

            $statusCode = Response::HTTP_OK;
            $recordset = ['customer' => [
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'gender' => $customer->getGender(),
                'birthdate' => $customer->getBirthday()
            ]];

            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Ok', $recordset);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $this->logger->error($exception->getMessage());

            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, '', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }
    }

    /**
     * Return Customer by email.
     * @param $email
     * @return Customer|null
     */
    private function getCustomer($email): ?Customer
    {
        /** @var Customer|null $customer */
        $customer = $this->entityManager->getRepository('App:Customer\Customer')
            ->findOneBy(['email' => $email]);

        return $customer;
    }

    /**
     * Validate gender is valid.
     * @param $gender
     * @return bool
     */
    private function validateGender($gender): bool
    {
        if (in_array($gender, [
            CustomerInterface::UNKNOWN_GENDER,
            CustomerInterface::FEMALE_GENDER,
            CustomerInterface::MALE_GENDER,
        ])) {
            return true;
        }

        return false;
    }

    private function validateDate($birthdate): bool
    {
        $array  = explode('-', $birthdate);
        if (checkdate($array[1], $array[2], $array[0])) {
            return true;
        }

        return false;
    }
}
