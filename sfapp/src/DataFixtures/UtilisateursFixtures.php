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

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    private UserPasswordHasherInterface $passwordHasher;

    public function load(ObjectManager $manager): void
    {
        $bat=$this->getReference(BatimentFixtures::BATIMENT_D, Batiment::class);


        $d307 = $this->make_Salle("D307", $bat->getEtages()[3], 3, 5);
        $manager->persist($d307);

        // Créer une salle avec l'ID 15        // Créer un technicien
        $technicien = new Utilisateur();
        $technicien->setNom('Dupont');
        $technicien->setPrenom('Jean');
        $technicien->setEmail('jean.dupont@example.com');
        $technicien->setAdresse('123 Rue Exemple');
        $technicien->setRoles(['ROLE_TECHNICIEN']);
        $technicien->setPassword($this->passwordHasher->hashPassword($technicien, 'password123'));
        $technicien->generateUsername(); // Génération automatique du username
        $manager->persist($technicien);

        $technicien = new Utilisateur();
        $technicien->setNom('Dupond');
        $technicien->setPrenom('Maxime');
        $technicien->setEmail('maxime.dupond@example.com');
        $technicien->setAdresse('1 rue de la petite etoile lorgnac ');
        $technicien->setRoles(['ROLE_TECHNICIEN']);
        $technicien->setPassword($this->passwordHasher->hashPassword($technicien, '1234'));
        $technicien->generateUsername(); // Génération automatique du username
        $manager->persist($technicien);

        $technicien = new Utilisateur();
        $technicien->setNom('Axaz');
        $technicien->setPrenom('Max');
        $technicien->setEmail('Max.Axaz@example.com');
        $technicien->setAdresse('2 rue de la petite etoile lorgnac ');
        $technicien->setRoles(['ROLE_CHARGE_DE_MISSION']);
        $technicien->setPassword($this->passwordHasher->hashPassword($technicien, '12345'));
        $technicien->generateUsername(); // Génération automatique du username
        $manager->persist($technicien);

        $technicien = new Utilisateur();
        $technicien->setNom('Benito');
        $technicien->setPrenom('Benoit');
        $technicien->setEmail('benoit.benito@example.com');
        $technicien->setAdresse('2 rue de la petite etoile lorgnac ');
        $technicien->setRoles(['ROLE_TECHNICIEN']);
        $technicien->setPassword($this->passwordHasher->hashPassword($technicien, '12345'));
        $technicien->generateUsername(); // Génération automatique du username
        $manager->persist($technicien);

        // Créer un détail d'intervention pour le technicien et la salle
        $detailIntervention = new DetailIntervention();
        $detailIntervention->setTechnicien($technicien);
        $detailIntervention->setSalle($d307);
        $detailIntervention->setDescription("le Sa ne marche plus ");
        $detailIntervention->setDateAjout(new \DateTime());
        $manager->persist($detailIntervention);

        $detailIntervention = new DetailIntervention();
        $detailIntervention->setTechnicien($technicien);
        $detailIntervention->setSalle($d307);
        $detailIntervention->setDescription("plus de données de capteur");
        $detailIntervention->setDateAjout(new \DateTime());
        $manager->persist($detailIntervention);

        // Exécuter les persistes
        $manager->flush();
    }
    public function make_Salle(string $nom, Etage $e, int $fen, int $rad) : Salle
    {
        $salle = new Salle();
        $salle->setNom($nom);
        $salle->setFenetre($fen);
        $salle->setRadiateur($rad);
        $salle->setEtage($e);
        return $salle;
    }


    public function getDependencies(): array
    {
        return [
            SalleFixtures::class, // Assurez-vous que les salles sont persistées avant cette fixture
        ];
    }
}
