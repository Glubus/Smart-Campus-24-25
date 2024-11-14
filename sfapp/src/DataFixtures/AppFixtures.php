<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $batimentC = new Batiment();
        $batimentC->setNom('C');
        $batimentC->setAdresse('15 Rue François de Vaux de Foletier, 17000 La Rochelle');
        $manager->persist($batimentC);

        $batimentD = new Batiment();
        $batimentD->setNom('D');
        $batimentD->setAdresse('15 Rue François de Vaux de Foletier, 17000 La Rochelle');
        $manager->persist($batimentD);

        $manager->flush();
    }
}
