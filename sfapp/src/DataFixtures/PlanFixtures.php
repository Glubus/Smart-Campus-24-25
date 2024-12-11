<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\DetailPlan;
use App\Entity\Plan;
use App\Entity\SA;
use App\Entity\Salle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlanFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $batiment = new Batiment();
        $batiment->setNom("Batiment D");
        $batiment->setAdresse("15, rue vaux de foletier");
        $batiment->setNbEtages(4);
        $manager->persist($batiment);
        $plan = new Plan();
        $plan->setNom("Prototype 1");
        $plan->setDate(new \DateTime());
        $plan->setBatiment($batiment);
        $manager->persist($plan);

        $sa = new SA();
        $sa->setNom("ESP-003");
        $sa->setDateAjout(new \DateTime());
        $manager->persist($sa);

        $salle= new Salle();
        $salle->setNom("D301");
        $salle->setBatiment($batiment);
        $salle->setEtage(4);
        $salle->setFenetre(4);
        $salle->setRadiateur(5);
        $manager->persist($salle);

        $detail= new DetailPlan();
        $detail->setPlan($plan);
        $detail->setSA($sa);
        $detail->setSalle($salle);
        $detail->setDateAjout(new \DateTime());
        $manager->persist($detail);
        $manager->flush();
    }
}
