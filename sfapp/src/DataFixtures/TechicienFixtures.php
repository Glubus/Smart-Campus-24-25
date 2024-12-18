<?php

namespace App\DataFixtures;

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
        // Créer une salle avec l'ID 15
        $salle = new Salle();
        $salle->setId(15); // Forcer l'ID (attention à l'utilisation d'un générateur personnalisé)
        $salle->setNom('Salle 15');
        $manager->persist($salle);

        // Créer un technicien
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
        $detailIntervention->setSalle($salle);
        $detailIntervention->setEtat(EtatIntervention::EN_COURS); // Exemple : état "en cours"
        $manager->persist($detailIntervention);

        // Exécuter les persistes
        $manager->flush();
    }
    }
}
