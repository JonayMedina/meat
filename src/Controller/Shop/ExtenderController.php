<?php


namespace App\Controller\Shop;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExtenderController extends AbstractController
{
    /**
     *
     * @Route("/favorites", name="user_favorites")
     * @return Response
     */
    public function favoritesAction()
    {
        return $this->render('shop/account/favorites.html.twig');
    }
}
