<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class CouponController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class CouponController extends AbstractController
{
    /**
     *
     * @Route("/coupon", name="coupons_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/coupon/index.html.twig');
    }
}
