<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class SegmentController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class SegmentController extends AbstractController
{
    /**
     *
     * @Route("/segment", name="segments_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/segment/index.html.twig');
    }
}
