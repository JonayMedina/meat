<?php

namespace App\Controller\Shop;

use App\Entity\User\ShopUser;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use App\Form\Shop\BillingProfileType;
use App\Repository\FavoriteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sylius\Component\Core\Repository\AddressRepositoryInterface;
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

    /**
     * @Route("/account/billing", name="user_billing")
     * @param Request $request
     * @param AddressRepositoryInterface $addressRepository
     * @return Response
     */
    public function updateBillingAction(Request $request, AddressRepositoryInterface $addressRepository) {
        /** @var ShopUser $user */
        $user = $this->getUser();
        /** @var Customer $customer */
        $customer = $user->getCustomer();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(BillingProfileType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address = $customer->getDefaultBillingAddress();

            if ($address instanceof Address) {
                $address->setCustomer($customer);
                $address->setType(Address::TYPE_BILLING);
                $customer->setDefaultBillingAddress($address);
            }

            $em->flush();

            return $this->redirectToRoute('user_billing', ['success' => true]);
        }

        return $this->render('shop/account/updateBilling.html.twig', ['form' => $form->createView()]);
    }
}
