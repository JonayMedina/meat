<?php

namespace App\Controller\Admin;

use App\Service\DashboardService;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Request $request
     * @return Response
     */
    public function indexAction(DashboardService $dashboardService, Request $request)
    {
        $startDate = $request->get('start', date('d/m/Y', strtotime(DashboardService::START_DATE_MODIFIER, time())));
        $endDate = $request->get('end', date('d/m/Y'));

        return $this->render('/admin/dashboard/index.html.twig', [
            'dashboard' => $dashboardService
                ->setStartDate($startDate)
                ->setEndDate($endDate)
                ->recalculate()
        ]);
    }
}
