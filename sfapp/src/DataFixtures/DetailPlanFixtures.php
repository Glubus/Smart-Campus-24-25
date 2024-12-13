<?php

namespace App\DataFixtures;

use App\Entity\DetailPlan;
use App\Entity\Plan;
use App\Entity\SA;
use App\Entity\Salle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DetailPlanFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $salle=$this->getReference(SalleFixtures::D001, Salle::class);
        $sa=$this->getReference(SAFixtures::ESP_001, SA::class);
        $plan=$this->getReference(PlanFixtures::PLAN_1, Plan::class);
        $detail_plan=$this->ajouterDetail($plan,$sa, $salle);
        $manager->persist($detail_plan);

        $salle=$this->getReference(SalleFixtures::D003, Salle::class);
        $sa=$this->getReference(SAFixtures::test01, SA::class);
        $detail_plan=$this->ajouterDetail($plan,$sa, $salle);
        $manager->persist($detail_plan);

        $sa=$this->getReference(SAFixtures::test02, SA::class);
        $detail_plan=$this->ajouterDetail($plan,$sa, $salle);
        $manager->persist($detail_plan);

        $manager->flush();
    }

    public function ajouterDetail(Plan $plan, SA $sa, Salle $salle): DetailPlan{
        $DetailPlan=new DetailPlan();
        $DetailPlan->setPlan($plan);
        $DetailPlan->setSA($sa);
        $DetailPlan->setSalle($salle);
        $DetailPlan->setDateAjout(new \DateTime());
        return $DetailPlan;
    }
    public function getDependencies() : array{
        return array(
            SalleFixtures::class,
            PlanFixtures::class,
            SAFixtures::class,
        );
    }
}
