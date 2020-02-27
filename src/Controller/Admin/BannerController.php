<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class BannerController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class BannerController extends AbstractController
{
    /**
     *
     * @Route("/banner", name="banners_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/banner/index.html.twig');
    }
}
