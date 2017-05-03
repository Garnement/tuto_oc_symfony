<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        return $this->render('CoreBundle::index.html.twig');
    }

    /**
    * @Route("/contact", name="contact")
    */

    public function contactAction(Request $request)
    {
        $this->addFlash('info', 'La page de contact n\'est pas encore disponible');

        return $this->redirectToRoute('home');
    }
}
