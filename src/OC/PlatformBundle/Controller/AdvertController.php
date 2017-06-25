<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Form\ApplicationType;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Antispam;
use OC\PlatformBundle\Purger;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if($page < 1)
        {
            // Envoi d'une erreur en cas de page inférieur à 1
            throw new NotFoundHttpException('Page" '.$page.' " inexsistante.');
        }

        // Nb d'annonce par page
        // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
        $nbPerPage = 3;

        $listAdverts = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('OCPlatformBundle:Advert')
                     ->getAdverts($page, $nbPerPage);
        
        // Calcul du nb de page grâce au count qui retourne le nb total d'annonces
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        if ($page > $nbPages)
        {
            throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        return $this->render('OCPlatformBundle:Advert:index.html.twig', array('listAdverts' => $listAdverts,
                                                                              'nbPages'     => $nbPages,
                                                                              'page'        => $page));
    }

    public function viewAction($id, Request $request) 
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        $categories = $em->getRepository('OCPlatformBundle:Category')->findAll();

        $application = new Application();

        $form = $this->get('form.factory')->create(ApplicationType::class, $application);

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
        
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {
            // $em->persist($application);
            // $em->flush();

            // $request->getSession()->getFlashBag()->add('notice', 'Candidature enregistrée');

            // return $this->redirectToRoute('oc_platform_view', array('id' => $id));
        }
                              
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array('advert'          => $advert, 
                                                                             'listApplication' => $listApplication,
                                                                             'categories'      => $categories,
                                                                             'listAdvertSkill' => $listAdvertSkills,
                                                                             'form'            => $form->createView()) );
    }

    public function addAction(Request $request)
    {
        // Récupération du service Antispam
        $antispam = $this->get('oc_platform.antispam');

        // Récupération de l'EntityManager
        $em = $this->getDoctrine()->getManager();

        $advert = new Advert();
        
        $form = $this->get('form.factory')->create(AdvertType::class, $advert);
        
        if($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {
                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée');

                return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig', array( 'form' => $form->createView()));
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

        $form = $this->createForm(AdvertEditType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) 
        {
            
            // Entité récupérée via Doctrine donc inutile de persister
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');      
            return $this->redirectToRoute('oc_platform_view', array('id' => $id));
        }

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
        'advert' => $advert,
        'form'   => $form->createView()
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
        // On crée un form vide qui ne contient que CSRF
        $form = $this->createForm();

        if($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'L\'annonce a été supprimée');

            return $this->redirectToRoute('oc_platform_home');
        }

        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
            'advert' => $advert,
            'form'   => $form->createView()
        ));
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

  public function purgeAction($days, Request $request)
  {
    // On récupère le service de purge
    $purgerAdvert = $this->get('oc_platform.purger.advert');

    // On lance la purge
    $purgerAdvert->purge($days);

    // MEssage flash
    $request->getSession()->getFlashBag()->add('info', 'Annonces purgées');

    // Redirection vers l'accueil général
    return $this->redirectToRoute('oc_core_home');
  }  
}