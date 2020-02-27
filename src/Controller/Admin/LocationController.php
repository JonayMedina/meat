<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class LocationController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class LocationController extends AbstractController
{
    /**
     *
     * @Route("/location", name="locations_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/location/index.html.twig');
    }
}
