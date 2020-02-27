<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class FAQController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class FAQController extends AbstractController
{
    /**
     *
     * @Route("/faq", name="faqs_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/faq/index.html.twig');
    }
}
