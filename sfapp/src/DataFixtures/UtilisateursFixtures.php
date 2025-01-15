<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;
use App\Entity\Batiment;
use App\Entity\Etage;
use App\Entity\Utilisateur;
use App\Entity\DetailIntervention;
use App\Entity\Salle;

class UtilisateursFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Faker pour générer des données en français

        // Récupération des références des salles
        $salles = [
            $this->getReference(SalleFixtures::D205,Salle::class), // Salle 205
            $this->getReference(SalleFixtures::D206,Salle::class), // Salle 206
            $this->getReference(SalleFixtures::D207,Salle::class), // Salle 207
            $this->getReference(SalleFixtures::D204,Salle::class), // Salle 204
            $this->getReference(SalleFixtures::D203,Salle::class), // Salle 203
        ];

        $techniciens = [];

        // Création de 10 techniciens aléatoires
        for ($i = 1; $i <= 10; $i++) {
            $technicien = new Utilisateur();
            $technicien->setNom($faker->lastName);
            $technicien->setPrenom($faker->firstName);
            $technicien->setEmail($faker->unique()->email);
            $technicien->setAdresse('123 Rue Exemple ' . $i);
            if ($i <= 5) {
                // Les 5 premiers avec le rôle "ROLE_TECHNICIEN"
                $technicien->setRoles(['ROLE_TECHNICIEN']);
            } else {
                // Les autres 5 avec le rôle "ROLE_CHARGE_DE_MISSION"
                $technicien->setRoles(['ROLE_CHARGE_DE_MISSION']);
            }

            $technicien->setPassword($this->passwordHasher->hashPassword($technicien, 'password123'));
            $technicien->generateUsername();
            $techniciens[] = $technicien;

            $manager->persist($technicien);
        }

        // Création de détails d'intervention aléatoires
        for ($k = 0; $k < 15; $k++) {
            $detailIntervention = new DetailIntervention();
            $detailIntervention->setTechnicien($techniciens[array_rand($techniciens)]); // Choisir un technicien au hasard
            $detailIntervention->setSalle($salles[array_rand($salles)]); // Choisir une salle au hasard
            $detailIntervention->setDescription($faker->sentence); // Description aléatoire
            $detailIntervention->setDateAjout($faker->dateTimeThisYear);
            $manager->persist($detailIntervention);
        }

        // Enregistrement des entités
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SalleFixtures::class, // Assurez-vous que les salles sont persistées avant cette fixture
        ];
    }
}
