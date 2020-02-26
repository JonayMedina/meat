<?php

namespace App\Controller\Admin;

use App\Service\DashboardService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class DashboardService
 * @package App\Controller\Admin
 */
class Dashboard extends AbstractController
{
    /**
     *
     * @Route("/", name="dashboard_index")
     * @param DashboardService $dashboardService
     * @return Response
     */
    public function indexAction(DashboardService $dashboardService)
    {
        return $this->render('/admin/dashboard/index.html.twig', [
            'dashboard' => $dashboardService
        ]);
    }
}
