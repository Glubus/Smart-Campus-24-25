<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BatimentFixtures extends Fixture
{
    public const BATIMENT_A = 'Batiment A';
    public const BATIMENT_C = 'Batiment C';
    public const BATIMENT_D = 'Batiment D';

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $A=$this->make_batiment("A",3);
        $C=$this->make_batiment("C",3);
        $D=$this->make_batiment("D",3);

        $manager->persist($A);
        $manager->persist($C);
        $manager->persist($D);
        $this->addReference(self::BATIMENT_A, $A);
        $this->addReference(self::BATIMENT_C, $C);
        $this->addReference(self::BATIMENT_D, $D);

        $manager->flush();
    }


    public function make_batiment(string $char, int $e) : Batiment
    {
        $A = new Batiment();
        $A->setNom("Batiment ".$char);
        $A->setNbEtages($e);
        $A->setAdresse("15, rue vaux de foletier 17000 La Rochelle");
        return $A;
    }
}
