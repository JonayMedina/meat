<?php

namespace App\Controller\FrontendApi;

use App\Controller\ShopApi\ContactUsController;
use App\Entity\Product\Product;
use App\Entity\User\ShopUser;
use App\Model\APIResponse;
use App\Service\CaptchaVerificationService;
use App\Service\FavoriteService;
use App\Service\SettingsService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Product\Repository\ProductRepositoryInterface;
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
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @var FavoriteService
     */
    private $favoriteService;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ContactUsController $contactUsController
     */
    private $contactUsController;

    /**
     * QueueController constructor.
     * @param SenderInterface $sender
     * @param TranslatorInterface $translator
     * @param CaptchaVerificationService $captchaVerification
     * @param SettingsService $settingsService
     * @param FavoriteService $favoriteService
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(SenderInterface $sender, TranslatorInterface $translator, CaptchaVerificationService $captchaVerification, SettingsService $settingsService, FavoriteService $favoriteService, ProductRepositoryInterface $productRepository, ContactUsController $contactUsController)
    {
        $this->sender = $sender;
        $this->translator = $translator;
        $this->captchaVerification = $captchaVerification;
        $this->settingsService = $settingsService;
        $this->favoriteService = $favoriteService;
        $this->productRepository = $productRepository;
        $this->contactUsController = $contactUsController;
    }

    /**
     * @Route(
     *     "/message.{_format}",
     *     name="store_api_messages",
     *     methods={"POST"},
     *     options={"expose" = true}
     * )
     * @param Request $request
     * @return Response
     */
    public function newMessageAction(Request $request) {
        $data = json_decode($request->getContent(), true);

        if ($this->captchaVerification->verify($data['captcha_code'])) {
            return $this->contactUsController->newAction($request);
        } else {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $data = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Error', [
                'title' => $this->translator->trans('app.ui.contact_us.error'),
                'message' => $this->translator->trans('app.ui.captcha.error.message')
            ]);
        }

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/favorites.{_format}",
     *     name="store_api_favorites_add",
     *     methods={"POST"},
     *     options={"expose" = true}
     * )
     * @param Request $request
     */
    public function addFavoriteAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /**
         * @var ShopUser $user
         */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $op = 'error';

        if (isset($data)) {
            /**
             * @var Product $product
             */
            $product = $this->productRepository->find($data['product']);

            if ($product) {
                if ($this->favoriteService->addFavorite($product, $user)) {
                    $op = 'success';
                } else {
                    $op = 'error';
                }
            } else {
                $op = 'non-product';
            }
        } else {
            $op = 'error';
        }

        switch($op){
            case 'success':
                $statusCode = Response::HTTP_OK;
                $data = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Ok', [
                    'title' => $this->translator->trans('app.api.favorites.add.success')
                ]);
                break;
            case 'non-product':
                $statusCode = Response::HTTP_BAD_REQUEST;
                $data = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Error', [
                    'title' => $this->translator->trans('app.api.favorites.add.error.title'),
                    'message' => $this->translator->trans('app.api.favorites.add.error.non_existent_product'),
                ]);
                break;
            case 'error':
            default:
                $statusCode = Response::HTTP_BAD_REQUEST;
                $data = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Error', [
                    'title' => $this->translator->trans('app.api.favorites.add.error.title'),
                    'message' => $this->translator->trans('app.api.favorites.add.error.message'),
                ]);
        }

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }

    /**
     * @Route(
     *     "/favorites/delete.{_format}",
     *     name="store_api_favorites_remove",
     *     methods={"DELETE"},
     *     options={"expose" = true}
     * )
     * @param Request $request
     */
    public function removeFavoriteAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /**
         * @var ShopUser $user
         */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $op = 'error';

        if (isset($data)) {
            /**
             * @var Product $product
             */
            $product = $this->productRepository->find($data['product']);

            if ($product) {
                if ($this->favoriteService->removeFavorite($product, $user)) {
                    $op = 'success';
                } else {
                    $op = 'error';
                }
            } else {
                $op = 'non-product';
            }
        } else {
            $op = 'error';
        }

        switch($op){
            case 'success':
                $statusCode = Response::HTTP_OK;
                $data = new APIResponse($statusCode, APIResponse::TYPE_INFO, 'Ok', [
                    'title' => $this->translator->trans('app.api.favorites.remove.success')
                ]);
                break;
            case 'non-product':
                $statusCode = Response::HTTP_BAD_REQUEST;
                $data = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Error', [
                    'title' => $this->translator->trans('app.api.favorites.remove.error.title'),
                    'message' => $this->translator->trans('app.api.favorites.remove.error.non_existent_product'),
                ]);
                break;
            case 'error':
            default:
                $statusCode = Response::HTTP_BAD_REQUEST;
                $data = new APIResponse($statusCode, APIResponse::TYPE_ERROR, 'Error', [
                    'title' => $this->translator->trans('app.api.favorites.remove.error.title'),
                    'message' => $this->translator->trans('app.api.favorites.remove.error.message'),
                ]);
        }

        $view = $this->view($data, $statusCode);

        return $this->handleView($view);
    }
}
