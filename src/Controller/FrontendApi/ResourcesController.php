<?php

namespace App\Controller\FrontendApi;

use App\Model\APIResponse;
use App\Service\CaptchaVerificationService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResourcesController extends AbstractFOSRestController
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
     * @var CaptchaVerificationService
     */
    private $captchaVerification;

    /**
     * QueueController constructor.
     * @param SenderInterface $sender
     * @param TranslatorInterface $translator
     * @param CaptchaVerificationService $captchaVerification
     */
    public function __construct(SenderInterface $sender, TranslatorInterface $translator, CaptchaVerificationService $captchaVerification)
    {
        $this->sender = $sender;
        $this->translator = $translator;
        $this->captchaVerification = $captchaVerification;
    }

    /**
     * @Route(
     *     "/message.{_format}",
     *     name="store_api_messages",
     *     methods={"POST"}
     * )
     * @param Request $request
     * @return Response
     */
    public function newMessageAction(Request $request) {
        $data = json_decode($request->getContent(), true);

        if (isset($data)) {
            if ($this->captchaVerification->verify($data['captcha_code'])) {
                try {
                    $this->sender->send('message_received', ["rroca@praga.ws"], ['name' => $data['name'], 'email' => $data['email'], "message" => $data['message']]);
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
                    'message' => $this->translator->trans('app.ui.captcha.error.message')
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
