<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\EtageSalle;
use App\Entity\EtatAssignation;
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
        $batimentC->setNbEtages(4);
        $batimentC->setAdresse('15 Rue François de Vaux de Foletier, 17000 La Rochelle');
        $manager->persist($batimentC);

        $batimentD = new Batiment();
        $batimentD->setNom('D');
        $batimentD->setNbEtages(4);
        $batimentD->setAdresse('13 Rue François de Vaux de Foletier, 17000 La Rochelle');
        $manager->persist($batimentD);

        $D001 = new Salle();
        $D001->setBatiment($batimentD);
        $D001->setEtage(0);
        $D001->setNom("D001");
        $manager->persist($D001);

        $D002 = new Salle();
        $D002->setBatiment($batimentD);
        $D002->setEtage(0);
        $D002->setNom("D002");
        $manager->persist($D002);

        $SA = new SA();
        $SA->setNom('SATest');
        $manager->persist($SA);

        $plan=new Plan();
        $plan->setSA($SA);
        $plan->setSalle($D002);
        $plan->setDateAjout(new DateTime());
        //$plan->setEtat(EtatAssignation::Actif);
        $manager->persist($plan);

        $plan=new Plan();
        $plan->setSA($SA);
        $plan->setSalle($D001);
        $plan->setDateAjout(new DateTime());
        $manager->flush();
    }
}
