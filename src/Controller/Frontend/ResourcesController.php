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
     * @Route("/terms-and-conditions", name="store_terms")
     * @return Response
     * @throws NonUniqueResultException
     */
    public function termsAndConditionsAction()
    {

        $repository = $this->getDoctrine()->getManager()->getRepository('App:TermsAndConditions');

        $terms = $repository->findLatest();
        return $this->render('/frontend/terms/_terms.html.twig', ['terms' => $terms]);
    }
}
