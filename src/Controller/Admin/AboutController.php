<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class AboutController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class AboutController extends AbstractController
{
    /**
     *
     * @Route("/about", name="about_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/about/index.html.twig');
    }
}
