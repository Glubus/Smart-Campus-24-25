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

class TechnicienFixtures extends Fixture
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
        $batiment->setNom('Batiment D');
        $batiment->setAdresse('15, rue vaux de foletier 17000 La Rochelle');
        $batiment->setNbEtages(4);
        $manager->persist($batiment);


        // Création de 3 étages pour le bâtiment
        $etages = [];
        for ($i = 0; $i <= 3; $i++) {
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
            for ($j = 1; $j <= 5; $j++) {
                $salle = $this->make_Salle(
                    $etage->getBatiment()->getNom() . '-' . $etage->getNiveau() . '0' . $j,
                    $etage,
                    random_int(1, 5), // Nombre aléatoire de fenêtres
                    random_int(1, 5)  // Nombre aléatoire de radiateurs
                );
                $salles[] = $salle;
                $manager->persist($salle);
            }
        }
        $techniciens = [];

        // Création de 10 techniciens aléatoires
        // Création de 10 techniciens aléatoires
        for ($i = 1; $i <= 10; $i++) {
            $technicien = new Utilisateur();
            $technicien->setNom($faker->lastName);
            $technicien->setPrenom($faker->firstName);
            $technicien->setEmail($faker->unique()->email);
            $technicien->setAdresse('123 Rue Exemple ' . $i); // Always set an address
            $technicien->setRoles(['ROLE_TECHNICIEN']);
            $technicien->setPassword($this->passwordHasher->hashPassword($technicien, 'password123'));
            $technicien->generateUsername();
            $techniciens[] = $technicien; // Add the created technicien to the array

            $manager->persist($technicien);

        }

        $technicien = new Utilisateur();
        $technicien->setNom('Axaz');
        $technicien->setPrenom('Max');
        $technicien->setEmail('Max.Axaz@example.com');
        $technicien->setAdresse('2 rue de la petite etoile lorgnac ');
        $technicien->setRoles(['ROLE_CHARGE_DE_MISSION']);
        $technicien->setPassword($this->passwordHasher->hashPassword($technicien, '12345'));
        $technicien->generateUsername(); // Génération automatique du username
        $manager->persist($technicien);


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
