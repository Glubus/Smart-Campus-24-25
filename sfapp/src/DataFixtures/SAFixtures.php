<?php

namespace App\DataFixtures;

use App\Entity\SA;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SAFixtures extends Fixture
{
    public const ESP_001 = 'ESP-001';

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $sa=$this->make_sa("ESP-001",new DateTime());
        $manager->persist($sa);
        $this->addReference(self::ESP_001, $sa);
        $manager->flush();
    }

    public function make_sa( string $nom, \DateTime $aDate) : SA
    {
        $sa = new SA();
        $sa->setNom($nom);
        $sa->setDateAjout($aDate);
        return $sa;
    }
}
