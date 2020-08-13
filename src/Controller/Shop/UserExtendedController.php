<?php

namespace App\Controller\Shop;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Bundle\UserBundle\Controller\UserController;
use Sylius\Bundle\UserBundle\Form\Model\ChangePassword;
use Sylius\Bundle\UserBundle\Form\Type\UserChangePasswordType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserExtendedController extends UserController
{
    public function changePasswordAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $encoder = $this->container->get('security.password_encoder');

        if (!$this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            throw new AccessDeniedException('You have to be registered user to access this section.');
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $error = null;

        $changePassword = new ChangePassword();
        $formType = $this->getSyliusAttribute($request, 'form', UserChangePasswordType::class);
        $form = $this->createResourceForm($configuration, $formType, $changePassword);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $form->handleRequest($request)->isValid()) {
            $data = $form->getData();

            // Check if new password is not the same of current
            if (!$encoder->isPasswordValid($user, $data->getNewPassword())) {
                return $this->handleChangePassword($request, $configuration, $user, $changePassword->getNewPassword());
            } else {
                $error = 'app.ui.form.user.change_password.not_the_same_of_current';
            }
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        return $this->container->get('templating')->renderResponse(
            $configuration->getTemplate('changePassword.html'),
            [
                'form' => $form->createView(),
                'error' => $error
            ]
        );
    }

    private function getSyliusAttribute(Request $request, string $attribute, $default = null)
    {
        $attributes = $request->attributes->get('_sylius');

        return $attributes[$attribute] ?? $default;
    }
}
