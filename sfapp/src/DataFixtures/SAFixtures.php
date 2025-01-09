<?php

namespace App\DataFixtures;

use App\Entity\SA;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SAFixtures extends Fixture
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
    ];
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        foreach (self::ASSOCIATIONS as $key => $values){
            $sa=$this->make_sa($values);
            $manager->persist($sa);
            $this->addReference($values, $sa);
        }

        $manager->flush();
    }

    public function make_sa( string $nom) : SA
    {
        $sa = new SA();
        $sa->setNom($nom);
        return $sa;
    }
}
