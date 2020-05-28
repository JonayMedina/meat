<?php

namespace App\Controller\Frontend;

use App\Entity\User\ShopUser;
use App\Entity\Taxonomy\Taxon;
use App\Service\ProductService;
use App\Entity\Product\Product;
use App\Form\Admin\TokenPasswordType;
use App\Repository\LocationRepository;
use App\Repository\PromotionBannerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Sylius\Component\Product\Repository\ProductRepositoryInterface;

class ResourcesController extends AbstractController
{
    /** @var $captchaKey string */
    private $captchaKey;

    /**
     * ResourcesController constructor.
     * @param $captchaKey
     */
    public function __construct($captchaKey) {
        $this->captchaKey = $captchaKey;
    }

    /**
     * @Route("/", name="store_homepage")
     * @return Response
     */
    public function indexAction()
    {
        return $this->redirectToRoute('sylius_shop_homepage');
    }

    /**
     * @Route("/terms", name="store_terms")
     * @return Response
     */
    public function termsAndConditionsAction()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('App:TermsAndConditions');

        return $this->render('/frontend/pages/widgets/_terms.html.twig', ['terms' => $repository->findLatest()]);
    }

    /**
     * @Route("/terms-and-conditions", name="store_terms_page")
     * @return Response
     */
    public function termsAndConditionsPageAction()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('App:TermsAndConditions');

        return $this->render('/frontend/pages/terms.html.twig', ['terms' => $repository->findLatest()]);
    }

    /**
     * @Route("/welcome", name="store_welcome")
     * @return Response
     */
    public function welcomeAction() {
        $this->get('session')->getFlashBag()->clear();

        return $this->render('/frontend/welcome.html.twig');
    }

    /**
     * @Route("/forgotten-password/token", name="store_set_token")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function setResetTokenAction(Request $request) {
        $this->get('session')->getFlashBag()->clear();
        $code = $request->get('code', '');

        $form = $this->createForm(TokenPasswordType::class, null,  ['code' => $code]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $form->get('token')->getData();

            /** @var ShopUser|null $user */
            $user = $this->getDoctrine()->getManager()->getRepository('App:User\ShopUser')->findOneBy(['passwordResetToken' => $token]);

            if ($user instanceof ShopUser) {
                return $this->redirectToRoute('sylius_shop_password_reset', ['token' => $token]);
            }

            return $this->render('/frontend/security/setResetToken.html.twig', ['form' => $form->createView(), 'error' => 'app.ui.reset_password.check_you_email.message']);
        }

        return $this->render('/frontend/security/setResetToken.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/work-with-us", name="store_work_with_us")
     * @return Response
     */
    public function workWithUsAction() {
        $this->get('session')->getFlashBag()->clear();

        return $this->render('/frontend/pages/workWithUs.html.twig');
    }

    /**
     * @Route("/about-us", name="store_about_us")
     * @return Response
     */
    public function aboutUsAction() {
        $this->get('session')->getFlashBag()->clear();

        return $this->render('/frontend/pages/aboutUs.html.twig');
    }

    /**
     * @Route("/faqs", name="store_faqs")
     * @return Response
     */
    public function faqsAction() {
        $this->get('session')->getFlashBag()->clear();
        $repository = $this->getDoctrine()->getManager()->getRepository('App:FAQ');

        $queryBuilder = $repository
            ->createQueryBuilder('faq')
            ->orderBy('faq.position', 'ASC');

        $faqs = $queryBuilder
            ->getQuery()
            ->getResult();

        return $this->render('/frontend/pages/faqs.html.twig', [
            'faqs' => $faqs
        ]);
    }

    /**
     * @Route("/wholesalers", name="store_wholesalers")
     * @return Response
     */
    public function wholesalersAction() {
        $this->get('session')->getFlashBag()->clear();

        return $this->render('/frontend/pages/wholesalers.html.twig');
    }

    /**
     * @Route("/locations", name="store_locations")
     * @param LocationRepository $locationRepository
     * @return Response
     */
    public function locationsAction(LocationRepository $locationRepository) {
        $this->get('session')->getFlashBag()->clear();

        return $this->render('/frontend/pages/locations.html.twig', ['locations' => $locationRepository->findAll(), 'captchaKey' => $this->captchaKey]);
    }

    /**
     * @Route("/categories", name="store_categories")
     * @param Request $request
     * @param TaxonRepositoryInterface $taxonRepository
     * @return Response
     */
    public function categoriesAction(Request $request, TaxonRepositoryInterface $taxonRepository) {
        /**
         * @var Taxon[] $categories
         */
        $categories = $taxonRepository->findRootNodes();
        $from = $request->query->get('from') ? $request->query->get('from') : 'home';
        $current = $request->query->get('current') ? $request->query->get('current') : null;

        return $this->render('/frontend/pages/widgets/_categories.html.twig', ['categories' => $categories, 'from' => $from, 'current' => $current]);
    }

    /**
     * @Route("/{code}/products", name="store_products_by_taxon")
     * @param Request $request
     * @param String $code
     * @param TaxonRepositoryInterface $taxonRepository
     * @param ProductRepositoryInterface $productRepository
     * @return Response
     */
    public function productsByTaxonAction(Request $request, String $code, TaxonRepositoryInterface $taxonRepository, ProductRepositoryInterface $productRepository) {
        $taxon = $taxonRepository->findOneBy(['code' => $code]);
        $products = [];
        $limit = $request->query->get('count');

        if ($taxon instanceof Taxon) {
            $products = $productRepository->findByTaxon($taxon->getId(), $limit);
        }

        return $this->render('/frontend/pages/widgets/taxon/_products.html.twig', ['products' => $products]);
    }

    /**
     * @Route("/offers", name="store_offers")
     * @param ProductRepositoryInterface $productRepository
     * @return Response
     */
    public function offersAction(ProductRepositoryInterface $productRepository) {
        $products = $productRepository->findOffers();

        return $this->render('/frontend/pages/widgets/_products.html.twig', ['products' => $products]);
    }

    /**
     * @Route("/banners", name="store_banners")
     * @param PromotionBannerRepository $promotionBannerRepository
     * @return Response
     */
    public function bannersAction(PromotionBannerRepository $promotionBannerRepository) {
        return $this->render('/frontend/pages/widgets/_banners.html.twig', ['banners' => $promotionBannerRepository->findAvailable()]);
    }

    /**
     * @Route("/search", name="store_search")
     * @param Request $request
     * @param $search
     * @param ProductRepositoryInterface $productRepository
     * @param ProductService $productService
     * @return Response
     */
    public function searchAction(Request $request, ProductRepositoryInterface $productRepository, ProductService $productService) {
        $list = [];
        $locale = $request->getLocale();
        $search = $request->query->get('search');

        if ($search) {
            /** @var Product[] $products */
            $products = $productRepository
                ->searchQuery($search, $locale)
                ->getQuery()
                ->getResult();

            $list = $products;
        } else {
            $search = '';
        }

        return $this->render('/frontend/pages/search.html.twig', ['results' => $list, 'search' => $search]);
    }
}
