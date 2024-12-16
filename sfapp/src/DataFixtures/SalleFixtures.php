<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\Salle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SalleFixtures extends Fixture implements DependentFixtureInterface
{
    public const D301 = 'D301';
    public const D001 = 'D001';
    public const D002 = 'D002';
    public const D003 = 'D003';

    public function load(ObjectManager $manager): void
    {
        $bat=$this->getReference(BatimentFixtures::BATIMENT_D, Batiment::class);

        $d301=$this->make_Salle($bat,"D301",3,3,5);
        $d001=$this->make_Salle($bat,"D001",0,3,3);
        $d002=$this->make_Salle($bat,"D002", 0,3,3);
        $d003=$this->make_Salle($bat,"D003",0,1,1);

        $manager->persist($d301);
        $manager->persist($d001);
        $manager->persist($d002);
        $manager->persist($d003);

        $this->addReference(self::D301, $d301);
        $this->addReference(self::D001, $d001);
        $this->addReference(self::D002, $d002);
        $this->addReference(self::D003, $d003);

        $manager->flush();
    }

    public function make_Salle(Batiment $b, string $nom, int $e, int $fen, int $rad) : Salle
    {
        $salle = new Salle();
        $salle->setNom($nom);
        $salle->setFenetre($fen);
        $salle->setRadiateur($rad);
        $salle->setEtage($e);
        $salle->setBatiment($b);
        return $salle;
    }
    public function getDependencies() : array{
        return array(
            BatimentFixtures::class
        );
    }
}
