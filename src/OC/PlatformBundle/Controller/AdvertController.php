<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use OC\PlatformBundle\Antispam;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if($page < 1)
        {
            // Envoi d'une erreur en cas de page inférieur à 1
            throw new NotFoundHtttpException('Page" '.$page.' " inexsistante.');
        }

            // Notre liste d'annonce en dur
        $listAdverts = array(
        array(
            'title'   => 'Recherche développpeur Symfony',
            'id'      => 1,
            'author'  => 'Alexandre',
            'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
            'date'    => new \Datetime()),
        array(
            'title'   => 'Mission de webmaster',
            'id'      => 2,
            'author'  => 'Hugo',
            'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
            'date'    => new \Datetime()),
        array(
            'title'   => 'Offre de stage webdesigner',
            'id'      => 3,
            'author'  => 'Mathieu',
            'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
            'date'    => new \Datetime())
        );

        return $this->render('OCPlatformBundle:Advert:index.html.twig', array('listAdverts' => $listAdverts));
    }

    public function viewAction($id, Request $request) 
    {
        $advert = array( 'title' => 'Recherche dev Symfony',
                         'id' => $id,
                         'author' => 'Alexandre',
                         'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
                         'date' => new \DateTime()
                        );

        return $this->render('OCPlatformBundle:Advert:view.html.twig', array('advert' => $advert));
    }

    public function addAction(Request $request)
    {
        // Récupération du service Antispam
        $antispam = $this->get('oc_platform.antispam');

        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if ($request->isMethod('POST')) {

            if($antispam->isSpam($text))
            {
                throw new \Exception('Votre message a été détécté comme spam');
            }

        $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
        // Puis on redirige vers la page de visualisation de cettte annonce
        return $this->redirectToRoute('oc_platform_view', array('id' => 5));
        }
        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('OCPlatformBundle:Advert:add.html.twig');
    }

    public function editAction($id = null, Request $request)
    {
        if ($request->isMethod('POST')) {

        $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');      

        return $this->redirectToRoute('oc_platform_view', array('id' => $id));

        }

        $advert = array(
        'title'   => 'Recherche développpeur Symfony',
        'id'      => $id,
        'author'  => 'Alexandre',
        'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
        'date'    => new \Datetime()
        );

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
        'advert' => $advert
        ));
    }

    public function deleteAction($id)
    {
        return $this->render('OCPlatformBundle:Advert:delete.html.twig');
    }

    public function menuAction($limit)
    {
        $listAdverts = array(
        array('id' => 2, 'title' => 'Recherche développeur Symfony'),
        array('id' => 5, 'title' => 'Mission de webmaster'),
        array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
        'listAdverts' => $listAdverts
        ));
    }
}