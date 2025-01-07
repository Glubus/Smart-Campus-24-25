<?php

namespace App\DataFixtures;

use App\Entity\Batiment;
use App\Entity\Etage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Utilisateur;
use App\Entity\EtatIntervention;
use App\Entity\DetailIntervention;
use App\Entity\Salle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class TechicienFixtures extends Fixture
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

        // Créer un détail d'intervention pour le technicien et la salle
        $detailIntervention = new DetailIntervention();
        $detailIntervention->setTechnicien($technicien);
        $detailIntervention->setSalle($d307);
        $detailIntervention->setEtat(EtatIntervention::EN_COURS); // Exemple : état "en cours"
        $detailIntervention->setDateAjout(new \DateTime());
        $manager->persist($detailIntervention);

        $detailIntervention = new DetailIntervention();
        $detailIntervention->setTechnicien($technicien);
        $detailIntervention->setSalle($d307);
        $detailIntervention->setEtat(EtatIntervention::EN_ATTENTE); // Exemple : état "en cours"
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

}
