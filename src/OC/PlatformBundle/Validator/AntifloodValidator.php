<?php

namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AntifloodValidator extends ConstraintValidator
{
    private $requestStack;
    private $em;

    // Les arguments déclarés dans la définition du service arrivent au constructeur
    // On doit les enregistrer dans l'objet pour pouvoir s'en resservir dans la méthode validate()
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        // On récupère l'objet request en utilisant getCurrentRequest du service request_stack
        $request = $this->requestStack->getCurrentRequest();

        // On récupère l'IP
        $ip = $request->getClientIp();

        // On vérifie si l'ip a deja posté une candidature il y a moins de 15 secondes
        $isFlood = $this->em
                        ->getRepository('OCPlatformBundle:Application')
                        ->isFlood($ip, 15);
        // Cette méthode n'existe pas, utilisée à titre d'exemple

        if($isFlood)
        {
            $this->context->addViolation($constraint->message);
        }
        if(strlen($value) < 3)
        {
            // C'est cette ligne qui déclenche l'erreur pour le formulaire avec en argument, le message de la contrainte.
            $this->context->addViolation($constraint->message);
        }
    }
}