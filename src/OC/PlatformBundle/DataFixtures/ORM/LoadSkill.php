<?php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Skill;

class LoadSkill implements FixtureInterface
{
    // Dans l'argument de la méthode load, l'objet $manager est l'entitymanager
    public function load(ObjectManager $manager)
    {
        $names = array(
            'PHP',
            'Symfony',
            'C++',
            'Java',
            'Photoshop',
            'Blender'            
        );

        foreach($names as $name)
        {
            // Création de la catégorie
            $skill = new Skill();
            $skill->setName($name);

            $manager->persist($skill);
        }

        $manager->flush();
    }
}