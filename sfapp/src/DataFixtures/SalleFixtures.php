<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\Salle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SalleFixtures extends Fixture implements DependentFixtureInterface
{
    public const D003 = 'D003';
    public const D205 = 'D205';
    public const D206 = 'D206';
    public const D207 = 'D207';
    public const D204 = 'D204';
    public const D203 = 'D203';
    public const D303 = 'D303';
    public const D304 = 'D304';
    public const C101 = 'C101';
    public const D109 = 'D109';
    public const SECRETARIAT = 'Secrétariat';
    public const D001 = 'D001';
    public const D002 = 'D002';
    public const D004 = 'D004';
    public const C004 = 'C004';
    public const C007 = 'C007';

    public function load(ObjectManager $manager): void
    {
        $batD = $this->getReference(BatimentFixtures::BATIMENT_D, Batiment::class);
        $batC = $this->getReference(BatimentFixtures::BATIMENT_C, Batiment::class);

        // Création des salles
        $d003 = $this->make_Salle($batD, "D003", 0, 5, 5);
        $d205 = $this->make_Salle($batD, "D205", 2, 5, 5);
        $d206 = $this->make_Salle($batD, "D206", 2, 6, 5);
        $d207 = $this->make_Salle($batD, "D207", 2, 7, 5);
        $d204 = $this->make_Salle($batD, "D204", 2, 4, 5);
        $d203 = $this->make_Salle($batD, "D203", 2, 3, 5);
        $d303 = $this->make_Salle($batD, "D303", 3, 3, 5);
        $d304 = $this->make_Salle($batD, "D304", 3, 4, 5);
        $c101 = $this->make_Salle($batC, "C101", 1, 1, 3);
        $d109 = $this->make_Salle($batD, "D109", 1, 9, 3);
        $secretariat = $this->make_Salle($batD, "Secrétariat", 0, 0, 3);
        $d001 = $this->make_Salle($batD, "D001", 0, 1, 3);
        $d002 = $this->make_Salle($batD, "D002", 0, 2, 3);
        $d004 = $this->make_Salle($batD, "D004", 0, 4, 3);
        $c004 = $this->make_Salle($batC, "C004", 0, 4, 3);
        $c007 = $this->make_Salle($batC, "C007", 0, 7, 3);

        // Persist des salles
        $manager->persist($d003);
        $manager->persist($d205);
        $manager->persist($d206);
        $manager->persist($d207);
        $manager->persist($d204);
        $manager->persist($d203);
        $manager->persist($d303);
        $manager->persist($d304);
        $manager->persist($c101);
        $manager->persist($d109);
        $manager->persist($secretariat);
        $manager->persist($d001);
        $manager->persist($d002);
        $manager->persist($d004);
        $manager->persist($c004);
        $manager->persist($c007);

        // Ajout des références pour les liaisons futures
        $this->addReference(self::D003, $d003);
        $this->addReference(self::D205, $d205);
        $this->addReference(self::D206, $d206);
        $this->addReference(self::D207, $d207);
        $this->addReference(self::D204, $d204);
        $this->addReference(self::D203, $d203);
        $this->addReference(self::D303, $d303);
        $this->addReference(self::D304, $d304);
        //$this->addReference(self::C101, $c101);
        $this->addReference(self::D109, $d109);
        $this->addReference(self::SECRETARIAT, $secretariat);
        $this->addReference(self::D001, $d001);
        $this->addReference(self::D002, $d002);
        $this->addReference(self::D004, $d004);
        $this->addReference(self::C004, $c004);
        $this->addReference(self::C007, $c007);

        // Enregistrement en base de données
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
