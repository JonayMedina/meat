<?php

namespace App\Controller\AdminApi;

use App\Model\APIResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * SyncController
 * @Route("/queue")
 */
class SyncController extends AbstractFOSRestController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * SyncController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route(
     *     ".{_format}",
     *     name="api_queue_index",
     *     defaults={"_format": "json"},
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $limit = $request->get('limit', 10);

        $sql = "SELECT id, body, created_at
          FROM messenger_messages
          WHERE queue_name = 'sync' AND delivered_at IS NULL
          ORDER BY created_at ASC LIMIT " . $limit;

        try {
            $statusCode = Response::HTTP_OK;

            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();

            foreach ($data as $index => $datum) {
                $data[$index]['body'] = json_decode($datum['body'], true);
            }

            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Sync queue', $data);
        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $exception->getMessage(), []);
        }

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/{id}.{_format}",
     *     name="api_queue_deliver",
     *     defaults={"_format": "json"},
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @return Response
     */
    public function deliverAction(Request $request)
    {
        $id = $request->get('id');
        $queue = $this->getQueue($id);

        if (!$queue) {
            $statusCode = Response::HTTP_NOT_FOUND;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Entry not found', []);
            $view = $this->view($response, $statusCode);

            return $this->handleView($view);
        }

        $sql = "UPDATE messenger_messages SET delivered_at = NOW() WHERE id = " . $id;

        try {
            $statusCode = Response::HTTP_OK;

            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();

            $response = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Updated', []);
        } catch (\Exception $exception) {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $response = new APIResponse($statusCode, APIResponse::TYPE_ERROR, $exception->getMessage(), []);
        }

        $view = $this->view($response, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @param $id
     * @return array|boolean
     */
    private function getQueue($id)
    {
        $sql = "SELECT id, body, created_at
          FROM messenger_messages
          WHERE delivered_at IS NULL AND id = " . $id;

        try {
            $stmt = $this->entityManager->getConnection()->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetch();

            if (isset($data['body'])) {
                $data['body'] = json_decode($data['body'], true);
            }

            return $data;
        } catch (\Exception $exception) {
            return false;
        }
    }

}
