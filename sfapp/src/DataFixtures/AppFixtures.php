<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\EtageSalle;
use App\Entity\EtatAssignation;
use App\Entity\DetailPlan;
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
        /*
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

        $D003 = new Salle();
        $D003->setBatiment($batimentD);
        $D003->setEtage(0);
        $D003->setNom("D003");
        $manager->persist($D003);

        $SA = new SA();
        $SA->setNom('SATest');
        $manager->persist($SA);

        $plan = new Plan();
        $plan->setBatiment($batimentD);
        $plan->setNom("plan01");
        $plan->setDate(new DateTime());
        $manager->persist($plan);

        $detailPlan=new DetailPlan();
        $detailPlan->setSA($SA);
        $detailPlan->setSalle($D003);
        $detailPlan->setDateAjout(new DateTime());
        $detailPlan->setPlan($plan);
        $manager->persist($detailPlan);

        $SA = new SA();
        $SA->setNom('SA01');
        $manager->persist($SA);

        $detailPlan=new DetailPlan();
        $detailPlan->setSA($SA);
        $detailPlan->setSalle($D002);
        $detailPlan->setDateAjout(new DateTime());
        $detailPlan->setPlan($plan);
        $manager->persist($detailPlan);

        $SA = new SA();
        $SA->setNom('SA02');
        $manager->persist($SA);

        $detailPlan=new DetailPlan();
        $detailPlan->setSA($SA);
        $detailPlan->setSalle($D002);
        $detailPlan->setDateAjout(new DateTime());
        $detailPlan->setPlan($plan);
        $manager->persist($detailPlan);

        $manager->flush();*/
    }
}
