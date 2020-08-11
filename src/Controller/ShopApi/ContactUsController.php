<?php

namespace App\Controller\ShopApi;

use App\Model\APIResponse;
use App\Service\SettingsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Route;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;

/**
 * ContactUsController
 * @Route("/contact-us")
 */
class ContactUsController extends AbstractFOSRestController
{
    /**
     * @var SenderInterface
     */
    private $sender;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * ContactUsController constructor.
     * @param SenderInterface $sender
     * @param TranslatorInterface $translator
     * @param SettingsService $settingsService
     */
    public function __construct(SenderInterface $sender, TranslatorInterface $translator, SettingsService $settingsService)
    {
        $this->sender = $sender;
        $this->translator = $translator;
        $this->settingsService = $settingsService;
    }

    /**
     * @Route(
     *     ".{_format}",
     *     name="shop_api_new_contact_us",
     *     methods={"POST"},
     *     options={"expose" = true}
     * )
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request) {
        $data = json_decode($request->getContent(), true);

        if (isset($data)) {
            try {
                $this->sender->send('message_received', [$this->settingsService->getComplaintsEmail()], ['name' => $data['name'], 'email' => $data['email'], "comment" => $data['message']]);
                $this->sender->send('message_sent', [$data['email']]);

                $statusCode = Response::HTTP_OK;
                $data = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Ok', [
                    'title' => $this->translator->trans('app.ui.contact_us.success'),
                    'message' => $this->translator->trans('app.ui.contact_us.success.message')
                ]);
            } catch (\Exception $exception) {
                $statusCode = Response::HTTP_BAD_REQUEST;
                $data = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Error', [
                    'title' => $this->translator->trans('app.ui.contact_us.error'),
                    'message' => $this->translator->trans('app.ui.contact_us.error.message')
                ]);
            }
        } else {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $data = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Error', [
                'title' => $this->translator->trans('app.ui.contact_us.error'),
                'message' => $this->translator->trans('app.ui.contact_us.error.message')
            ]);
        }

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }
}
