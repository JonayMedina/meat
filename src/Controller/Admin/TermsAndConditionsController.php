<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class TermsAndConditionsController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class TermsAndConditionsController extends AbstractController
{
    /**
     *
     * @Route("/terms", name="terms_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/terms/index.html.twig');
    }
}
