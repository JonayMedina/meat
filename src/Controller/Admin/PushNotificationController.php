<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class PushNotificationController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class PushNotificationController extends AbstractController
{
    /**
     *
     * @Route("/push", name="push_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/push/index.html.twig');
    }
}
