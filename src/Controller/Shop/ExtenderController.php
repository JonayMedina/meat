<?php

namespace App\Controller\Shop;

use App\Entity\User\ShopUser;
use App\Repository\FavoriteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExtenderController extends AbstractController
{
    /**
     * @Route("/account/favorites", name="user_favorites")
     * @param FavoriteRepository $favoriteRepository
     * @return Response
     */
    public function favoritesAction(FavoriteRepository $favoriteRepository)
    {
        /**
         * @var ShopUser $user
         */
        $user = $this->getUser();

        return $this->render('shop/account/favorites.html.twig', ['favorites' => $favoriteRepository->findBy(['shopUser' => $user])]);
    }

    /**
     * @Route("/account/changed-password", name="user_changed_password")
     * @param Request $request
     * @return Response
     */
    public function changedPassword(Request $request) {
        return $this->redirectToRoute('sylius_shop_account_change_password', ['success' => true]);
    }
}
