<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\EtageSalle;
use App\Entity\Plan;
use App\Entity\SA;
use App\Entity\Salle;
use DateTime;
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

        $Salle = new Salle();
        $Salle->setBatiment($batimentC);
        $Salle->setEtage(EtageSalle::REZDECHAUSSEE);
        $Salle->setNumero("06");

        $SA = new SA();
        $SA->setNom('SATest');
        $manager->persist($SA);

        $plan=new Plan();
        $plan->setSA($SA);
        $plan->setSalle($Salle);
        $plan->setDateAjout(new DateTime());
        $manager->persist($plan);
        $manager->flush();
    }
}
