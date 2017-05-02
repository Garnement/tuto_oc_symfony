<?php

namespace OC\PlatformBundle\DoctrineListener;

use OC\PlatformBundle\Email\ApplicationMailer;
use OC\PlatformBundle\Entity\Application;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class ApplicationCreationListener {

     /**
     * @var ApplicationMailer
     */
    private $applicationMailer;

    public function __construct(ApplicationMailer $applicationMailer)
    {
        $this->applicationMailer = $applicationMailer;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        /************************************
        args est un objet LifeCycleEventArgs
                Il offre deux méthodes getObject et getObjectManager.
            getObject retourne l'entité sur laquelle l'évenement se produit.
            getObjectManager retourne l' "EntityManager" nécéssaire pour persister ou supprimer de nouvelles entités, intule dans notre cas ici.
        **************************************/

        $entity = $args->getObject();

        // Envoi d'un email que pour les entités "Application"
        if(!$entity instanceof Application)
        {
            return;
        }

        $this->applicationMailer->sendNewNotifications($entity);
    }
}