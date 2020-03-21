<?php


namespace App\Controller\Frontend;

use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResourcesController extends AbstractController
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

        return $this->render('/frontend/terms/_terms.html.twig', ['terms' => $repository->findLatest()]);
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

        return $this->render('/frontend/terms/page.html.twig', ['terms' => $repository->findLatest()]);
    }

    /**
     * @Route("/welcome", name="store_welcome")
     * @return Response
     */
    public function welcomeAction() {
        $this->get('session')->getFlashBag()->clear();

        return $this->render('/frontend/welcome.html.twig');
    }
}
