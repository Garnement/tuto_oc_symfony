<?php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Category;

class LoadCategory implements FixtureInterface
{
    // Dans l'argument de la méthode load, l'objet $manager est l'entitymanager
    public function load(ObjectManager $manager)
    {
        $names = array(
            'Developpement web',
            'Developpement mobile',
            'Graphisme',
            'Intégration',
            'Réseau'            
        );

        foreach($names as $name)
        {
            // Création de la catégorie
            $category = new Category();
            $category->setName($name);

            $manager->persist($category);
        }

        $manager->flush();
    }
}