<?php


namespace App\Controller\Frontend;

use App\Entity\Product\Product;
use App\Entity\Taxonomy\Taxon;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResourcesController extends Controller
{
    /**
     *
     * @Route("/terms", name="store_terms")
     * @return Response
     * @throws NonUniqueResultException
     */
    public function termsAndConditionsAction()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository('App:TermsAndConditions');

        return $this->render('/frontend/pages/widgets/_terms.html.twig', ['terms' => $repository->findLatest()]);
    }

    /**
     *
     * @Route("/terms-and-conditions", name="store_terms_page")
     * @return Response
     * @throws NonUniqueResultException
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
     * @return Response
     */
    public function setResetTokenAction(Request $request) {
        $this->get('session')->getFlashBag()->clear();

        return $this->render('/frontend/security/setResetToken.html.twig');
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
            ->createQueryBuilder('faq');

        $queryBuilder
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
     * @return Response
     */
    public function locationsAction() {
        $this->get('session')->getFlashBag()->clear();
        $repository = $this->getDoctrine()->getManager()->getRepository('App:Location');

        return $this->render('/frontend/pages/locations.html.twig', ['locations' => $repository->findAll()]);
    }

    /**
     * @Route("/categories", name="store_categories")
     */
    public function categoriesAction() {
        $repository = $this->container->get('sylius.repository.taxon');

        /**
         * @var Taxon[] $categories
         */
        $categories = $repository->findAll();

        return $this->render('/frontend/pages/widgets/_categories.html.twig', ['categories' => $categories]);
    }

    /**
     * @Route("/{code}/products", name="store_products_by_taxon")
     * @param String $code
     * @return Response
     */
    public function productsByTaxonAction(String $code) {
        $taxsRep = $this->container->get('sylius.repository.taxon');
        $taxon = $taxsRep->findOneBy(['code' => $code]);
        $products = [];

        if ($taxon instanceof Taxon) {
            $prodRep = $this->container->get('sylius.repository.product');
            $qb = $prodRep->createQueryBuilder('product');
            /**
             * @var Product[] $products
             */
            $products = $qb->select('p')
                ->from('App\Entity\Product\Product', 'p')
                ->innerJoin('App\Entity\Product\ProductTaxon', 'pt', 'p.id = pt.product')
                ->where('p.enabled like :true')
                ->andWhere('pt.taxon = :taxon')
                ->setParameter('true', 1)
                ->setParameter('taxon', $taxon->getId())
                ->getQuery()
                ->getResult();
        }

        return $this->render('/frontend/pages/widgets/_products.html.twig', ['products' => $products]);
    }
}
