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
    public const ASSOCIATIONS = [
        "D205" => "ESP-004",
        "D206" => "ESP-008",
        "D207" => "ESP-006",
        "D204" => "ESP-014",
        "D203" => "ESP-012",
        "D303" => "ESP-005",
        "D304" => "ESP-011",
        "C101" => "ESP-007",
        "D109" => "ESP-024",
        "Secrétariat" => "ESP-026",
        "D001" => "ESP-030",
        "D002" => "ESP-028",
        "D004" => "ESP-020",
        "C004" => "ESP-021",
        "C007" => "ESP-022"
    ]; // Injecté via le constructeur ou autowiring

    public function load(ObjectManager $manager): void
    {
        $plan=$this->getReference(PlanFixtures::PLAN_1, Plan::class);
        foreach (self::ASSOCIATIONS as $key => $values){
            $salle=$this->getReference($key, Salle::class);
            $sa=$this->getReference($values, SA::class);
            $detail_plan=$this->ajouterDetail($plan,$sa, $salle);
            $manager->persist($detail_plan);
        }
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
