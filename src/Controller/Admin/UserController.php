<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class UserController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class UserController extends AbstractController
{
    /**
     *
     * @Route("/user", name="users_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/user/index.html.twig');
    }
}
