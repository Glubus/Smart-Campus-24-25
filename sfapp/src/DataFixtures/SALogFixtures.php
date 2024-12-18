<?php

namespace App\DataFixtures;

use App\Entity\ActionLog;
use App\Entity\SA;
use App\Entity\SALog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Illuminate\Support\Facades\Date;

class SALogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $sa=$this->getReference(SAFixtures::ESP_001, SA::class);

        $salog = $this->make_salog($sa,new \DateTime(), ActionLog::AJOUTER);
        $salog1 = $this->make_salog($sa,new \DateTime(), ActionLog::MODIFIER);
        $salog2 = $this->make_salog($sa,new \DateTime(), ActionLog::MODIFIER);
        $manager->persist($salog);
        $manager->persist($salog1);
        $manager->persist($salog2);

        $manager->flush();
    }

    public function make_salog(SA $sa, \DateTime $date, ActionLog $action) : SALog{
        $log = new SAlog();
        $log->setSA($sa);
        $log->setDate($date);
        $log->setAction($action);
        return $log;
    }
    public function getDependencies(){
        return array(
            SAFixtures::class
        );
    }
}
