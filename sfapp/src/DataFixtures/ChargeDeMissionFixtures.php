<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\Etage;
use App\Entity\Utilisateur;
use App\Entity\DetailIntervention;
use App\Entity\Salle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class ChargeDeMissionFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Faker pour générer des données en français

        // Création des bâtiments et étages
        $batiment = new Batiment();
        $batiment->setNom('Batiment C');
        $batiment->setAdresse('10, rue de la République 75000 Paris');
        $batiment->setNbEtages(3);
        $manager->persist($batiment);

        // Création de 2 étages pour le bâtiment
        $etages = [];
        for ($i = 0; $i <= 2; $i++) {
            $etage = new Etage();
            $etage->setNom($i);
            $etage->setNiveau($i);
            $etage->setBatiment($batiment);
            $etages[] = $etage;
            $manager->persist($etage);
        }

        // Création de salles pour chaque étage
        $salles = [];
        foreach ($etages as $etage) {
            for ($j = 1; $j <= 3; $j++) {
                $salle = $this->make_Salle(
                    $etage->getBatiment()->getNom() . '-' . $etage->getNiveau() . '0' . $j,
                    $etage,
                    random_int(1, 3), // Nombre aléatoire de fenêtres
                    random_int(1, 3)  // Nombre aléatoire de radiateurs
                );
                $salles[] = $salle;
                $manager->persist($salle);
            }
        }
        $chargeDeMissions = [];

        // Création de 5 utilisateurs avec le rôle ROLE_CHARGE_DE_MISSION
        for ($i = 1; $i <= 5; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setNom($faker->lastName);
            $utilisateur->setPrenom($faker->firstName);
            $utilisateur->setEmail($faker->unique()->email);
            $utilisateur->setAdresse('456 Rue Exemple ' . $i); // Always set an address
            $utilisateur->setRoles(['ROLE_CHARGE_DE_MISSION']);
            $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, 'password123'));
            $utilisateur->generateUsername();
            $chargeDeMissions[] = $utilisateur; // Add the created utilisateur to the array

            $manager->persist($utilisateur);
        }

        // Création de détails d'intervention aléatoires
        for ($k = 0; $k < 10; $k++) {
            $detailIntervention = new DetailIntervention();
            $detailIntervention->setTechnicien($chargeDeMissions[array_rand($chargeDeMissions)]); // Choisir un utilisateur au hasard
            $detailIntervention->setSalle($salles[array_rand($salles)]); // Choisir une salle au hasard
            $detailIntervention->setDescription($faker->sentence); // Description aléatoire
            $detailIntervention->setDateAjout($faker->dateTimeThisYear);
            $manager->persist($detailIntervention);
        }

        // Enregistrement des entités
        $manager->flush();
    }

    private function make_Salle(string $nom, Etage $etage, int $fen, int $rad): Salle
    {
        $salle = new Salle();
        $salle->setNom($nom);
        $salle->setFenetre($fen);
        $salle->setRadiateur($rad);
        $salle->setEtage($etage);
        return $salle;
    }
}