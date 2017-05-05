<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
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

        $repo = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('OCPlatformBundle:Advert');
        
        $listAdverts = $repo->findAll();

        return $this->render('OCPlatformBundle:Advert:index.html.twig', array('listAdverts' => $listAdverts));
    }

    public function viewAction($id, Request $request) 
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        $categories = $em->getRepository('OCPlatformBundle:Category')->findAll();

        // Renvoi d'erreur si l'ID n'existe pas
        if(null === $advert)
        {
            throw new NotFoundHttpException("L'annonce n° ".$id." n'existe pas.");
        }

        // Récupération des candidatures
        $listApplication = $em->getRepository('OCPlatformBundle:Application')
                              ->findBy(array( 'advert' => $advert) );
        
        // Récupération de la liste des AdvertSkill
        $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')
                               ->findBy(array('advert' => $advert));
                              
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array('advert'          => $advert, 
                                                                             'listApplication' => $listApplication,
                                                                             'categories'      => $categories,
                                                                             'listAdvertSkill' => $listAdvertSkills) );
    }

    public function addAction(Request $request)
    {
        // Récupération du service Antispam
        $antispam = $this->get('oc_platform.antispam');

        // Récupération de l'EntityManager
        $em = $this->getDoctrine()->getManager();


        // Création de l'entité Advert
        $advert = new Advert();
        $advert->setTitle('Recherche developpeur Symfony v'.rand(1,999));
        $advert->setAuthor('Alexandre');
        $advert->setContent("Nous recherchons un developpeur Symfony débutant bla bla...");
        // On ne peut pas définir de date car ces attributs
        // sont définis automatiquement dans le constructeur

        // Création de l'entité Image
        $img = new Image();
        $img->setUrl("http://lorempicsum.com/simpsons/350/200/".rand(1,9));
        $img->setAlt('Job de rêve');

        // On lie l'image à l'annonce
        $advert->setImage($img);


        // Création de la première candidature
        $app = new Application();
        $app->setAuthor('Jean');
        $app->setContent('Je suis très qualifié.');

        // Création de la deuxième candidature
        $app2 = new Application();
        $app2->setAuthor('Lise');
        $app2->setContent('Ma formation est très complète.');

        // Récupération des compétences possibles
        $listSkills = $em->getRepository('OCPlatformBundle:Skill')->findAll();

        // Pour chaque compétence
        foreach ($listSkills as $skill)
        {
            // Création d'une nouvelle relation entre une annonce et une compétence
            $advertSkill = new AdvertSkill();

            // Liaison avec l'annonce courante
            $advertSkill->setAdvert($advert);

            // Liaison de la compétence, qui change ici dans la boucle foreach
            $advertSkill->setSkill($skill);
            
            // Réglage du niveau de compétence
            $advertSkill->setLevel('Expert');

            // On persiste cette entité de relation, propriétaire des deux autres relations
            $em->persist($advertSkill);
        }



        // Liaison entre les candidatures et les annonces
        $app->setAdvert($advert);
        $app2->setAdvert($advert);

        // Etape 1: On persiste l'entité
        $em->persist($advert);

        // Etape 1 bis: Si on n'avait pas défini cascade={"persist"}
        //              On devrait persister à la main l'entité Image
        //              $em->persist($img);

        // Etape 1 ter: Pour cette relation, pas de cascade lorsqu'on
        //              persiste Advert, car la relation est définie
        //              dans l'entité Application et non Advert.
        //              On doit donc tout persister à la main ici
        $em->persist($app);
        $em->persist($app2);


        //Etape 2: On flush tout ce qui a été persisté avant
        $em->flush();


        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if ($request->isMethod('POST')) {

            if($antispam->isSpam($text))
            {
                throw new \Exception('Votre message a été détécté comme spam');
            }

        $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
        // Puis on redirige vers la page de visualisation de cettte annonce
        return $this->redirectToRoute('oc_platform_view', array( 'id' => $advert->getId() ) );
        }
        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('OCPlatformBundle:Advert:add.html.twig');
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // Récupération de l'id
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert)
        {
            throw new NotFoundHttpException('L\'annonce n°\' '.$id.' n\'existe pas.');
        }

        $categories = $em->getRepository('OCPlatformBundle:Category')->findAll();

        // On boucle sur les catégories pour les lier à l'annonce
        foreach ($categories as $category)
        {
            //$advert->addCategory($category);
        }

        /* Pour persister le changement dans la relation, il faut persister l'entité propriétaire.
           Ici, Advert est le propriétaire, donc inutile de la persister car on l'a recupérée depuis Doctrine
        */

        $em->flush();


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
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert)
        {
            throw new NotFoundHttpException("L'annonce n° ".$id." n'existe pas.");
        }

        // On boucle sur les catégories pour les supprimer
        foreach($advert->getCategories() as $cat)
        {
            $advert->removeCategory($cat);
        }

        /* Ici l'entité propriétaire a eté récupérée depuis Docttrine,
           donc il n'est pas nécéssaire de persister
        */

        $em->flush();


        return $this->render('OCPlatformBundle:Advert:delete.html.twig');
    }

    public function menuAction($limit)
    {
        $repo = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('OCPlatformBundle:Advert');
        
        $listAdverts = $repo->findBy( array(),null, $limit);

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
        'listAdverts' => $listAdverts
        ));
    }

    public function editImageAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        // Modification de l'URL
        $advert->getImage()->setUrl('test.png');

        // Pas besoin de persister l'annonce ni l'image,
        // les entités sont automatiquement persistée car
        // elles ont été récupérées depuis Doctrine lui même

        // Modification
        $em->flush();

        return new Response('Image modifiée.');
    }

    public function listAction()
    {
        $listAdverts = $this->getDoctrine()
                            ->getManager()
                            ->getRepository('OCPlatformBundle:Advert')
                            ->getAdvertWithApplications();
        
        foreach($listAdverts as $advert)
        {
            $advert->getApplications();
        }
    }

    public function testSlugAction()
    {
        $advert = new Advert();
        $advert->setTitle('Recherche developpeur');

        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush(); // C'est à cet instant qu'est généré le slug

        return new Response('Slug généré: '. $advert->getSlug());
    }
}