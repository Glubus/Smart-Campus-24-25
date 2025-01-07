<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\Etage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $batA = $this->getReference(BatimentFixtures::BATIMENT_A, Batiment::class);

        for($i = 0; $i < $batA->getNbEtages(); $i++) {
            $batA->renameEtage($i, (string)($i - 2));
        }
        $batA->renameEtage(2, "Rez-de-chaussee");
        $manager->persist($batA);
        $manager->flush();
    }

    public function getDependencies() : array{
        return array(
            BatimentFixtures::class,
        );
    }
}
