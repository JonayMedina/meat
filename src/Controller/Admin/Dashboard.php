<?php

namespace App\Controller\Admin;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class Dashboard
 * @package App\Controller\Admin
 */
class Dashboard extends AbstractController
{
    /**
     *
     * @Route("/", name="dashboard_index")
     */
    public function indexAction()
    {
        return $this->render('/admin/dashboard/index.html.twig');
    }
}
