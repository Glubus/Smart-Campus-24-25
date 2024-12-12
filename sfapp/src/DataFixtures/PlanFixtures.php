<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\DetailPlan;
use App\Entity\Plan;
use App\Entity\SA;
use App\Entity\Salle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlanFixtures extends Fixture implements DependentFixtureInterface
{
    public const PLAN_1 = 'Plan_1';
    public function load(ObjectManager $manager): void
    {
        $bat=$this->getReference(BatimentFixtures::BATIMENT_D, Batiment::class);
        $plan=$this->make_plan("Prototype 1",new \DateTime(), $bat);
        $manager->persist($plan);
        $this->addReference(self::PLAN_1, $plan);

        $manager->flush();
    }

    public function make_plan(string $nom, \DateTime $aDate, Batiment $bat){
        $plan = new Plan();
        $plan->setNom($nom);
        $plan->setDate($aDate);
        $plan->setBatiment($bat);
        return $plan;
    }
    public function getDependencies() : array{
        return array(
            BatimentFixtures::class
        );
    }
}
