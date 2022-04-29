<?php

namespace App\Controller\ShopApi;

use App\Entity\ShopUserDevice;
use App\Model\APIResponse;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ShopUserDeviceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * DeviceController
 * @Route("/devices")
 */
class DeviceController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ShopUserDeviceRepository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DeviceController constructor.
     * @param EntityManagerInterface $entityManager
     * @param ShopUserDeviceRepository $repository
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, ShopUserDeviceRepository $repository, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * @Route(
     *     "/.{_format}",
     *     name="shop_api_new_device",
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $key = $request->get('key');
        $type = $request->get('type');

        /** @var ShopUser $user */
        $user = $this->getUser();

        if (empty($key)) {
            return $this->renderError('Invalid device identifier.');
        }

        $device = $this->repository->findOneBy(['key' => $key, 'user' => $user]);

        if (!$device instanceof ShopUserDevice) {
            $statusCode = Response::HTTP_CREATED;
            $message = 'Created.';

            $device = new ShopUserDevice();
            $device->setKey($key);
            $device->setType($type);
            $device->setUser($user);

            $this->entityManager->persist($device);
        } else {
            $statusCode = Response::HTTP_OK;
            $message = 'Updated.';

            $device->setType($type);
        }

        try {
            $this->entityManager->flush();

            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, $message, [
                'key' => $device->getKey(),
                'type' => $device->getType(),
            ]);

            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return $this->renderError($exception->getMessage());
        }

    }

    /**
     * @Route(
     *     "/{key}.{_format}",
     *     name="shop_api_delete_device",
     *     methods={"DELETE"}
     * )
     *
     * @param ShopUserDevice $shopUserDevice
     * @return Response
     */
    public function deleteAction(ShopUserDevice $shopUserDevice)
    {
        $this->entityManager->remove($shopUserDevice);

        try {
            $this->entityManager->flush();
            $statusCode = Response::HTTP_NO_CONTENT;

            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, '', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return $this->renderError($exception->getMessage());
        }
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
}
