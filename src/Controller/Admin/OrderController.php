<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class OrderController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class OrderController extends AbstractController
{
    /**
     *
     * @Route("/order", name="orders_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/order/index.html.twig');
    }
}
