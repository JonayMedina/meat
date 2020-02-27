<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class RatingController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class RatingController extends AbstractController
{
    /**
     *
     * @Route("/rating", name="ratings_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/rating/index.html.twig');
    }
}
