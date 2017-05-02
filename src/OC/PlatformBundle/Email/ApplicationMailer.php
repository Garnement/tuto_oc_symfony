<?php

namespace OC\PlatformBundle\Email;

use OC\PlatformBundle\Entity\Application;

class ApplicationMailer {
    
     /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(\Swift_Mailer $mailer, $container)
    {
        $this->mailer = $mailer;
        $this->container = $container;
    }

    public function sendNewNotifications(Application $application)
    {
        $body = $this->container->get('templating')->render('OCPlatformBundle:Emails:confirmation.html.twig');
        $msg = \Swift_Message::newInstance()
            ->setSubject('test')
            ->setFrom('admin@platform.com')
            ->setTo('jean@yopmail.com')
            ->setBody(
                $body, 'text/html');

        $this->container->get('mailer')->send($msg);
    }

}