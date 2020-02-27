<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class SettingsController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class SettingsController extends AbstractController
{
    /**
     *
     * @Route("/purchase/texts", name="purchase_texts")
     * @return Response
     */
    public function textsAction()
    {
        return $this->render('/admin/configuration/texts.html.twig');
    }

    /**
     *
     * @Route("/purchase/settings", name="purchase_settings")
     * @return Response
     */
    public function settingsAction()
    {
        return $this->render('/admin/configuration/settings.html.twig');
    }

    /**
     *
     * @Route("/holidays", name="holidays")
     * @return Response
     */
    public function holidaysAction()
    {
        return $this->render('/admin/configuration/holidays.html.twig');
    }

    /**
     *
     * @Route("/searcher", name="searcher")
     * @return Response
     */
    public function searcherAction()
    {
        return $this->render('/admin/configuration/searcher.html.twig');
    }

    /**
     *
     * @Route("/caregory-color", name="category_color")
     * @return Response
     */
    public function categoryColorAction()
    {
        return $this->render('/admin/configuration/category_color.html.twig');
    }
}
