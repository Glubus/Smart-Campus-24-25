<?php

namespace App\DataFixtures;

use App\Entity\ActionLog;
use App\Entity\Batiment;
use App\Entity\SA;
use App\Entity\Salle;
use App\Entity\SALog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\ValeurCapteur;
use App\Entity\Capteur;
use App\Entity\TypeCapteur;
use Faker\Factory;

class ValeurCapteurFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Initialisation de Faker pour générer des données réalistes
        $faker = Factory::create();
        $lastVal=array(20,50,400);
        $sa = new SA();
        $sa->setNom($faker->word);
        $sa->setDateAjout(new \DateTime());
        // Créer un tableau pour stocker les capteurs
        $capteurs = [];

        $salog = new SAlog();
        $salog->setSA($sa);
        $salog->setDate(new \DateTime());
        $salog->setAction(ActionLog::AJOUTER);

        $manager->persist($salog);

        $salog = new SAlog();
        $salog->setSA($sa);
        $salog->setDate(new \DateTime());
        $salog->setAction(ActionLog::MODIFIER);
        $manager->persist($salog);

        $salog1 = new SAlog();
        $salog1->setSA($sa);
        $salog1->setDate(new \DateTime());
        $salog1->setAction(ActionLog::MODIFIER);
        $manager->persist($salog);

        $salog2 = new SAlog();
        $salog2->setSA($sa);
        $salog2->setDate(new \DateTime());
        $salog2->setAction(ActionLog::MODIFIER);
        $manager->persist($salog);




        // Création des capteurs de type température, humidité, CO2
        $types = [TypeCapteur::TEMPERATURE, TypeCapteur::HUMIDITE, TypeCapteur::CO2];

        // Générer des valeurs pour chaque capteur sur un mois avec une fréquence toutes les 10 minutes
        $startDate = new \DateTime('2024-09-01 00:00:00');
        $endDate = new \DateTime('2024-12-31 00:00:00');
        $interval = new \DateInterval('PT10M'); // Intervalle de 10 minutes

        // On va générer des valeurs toutes les 10 minutes pendant un mois
        $period = new \DatePeriod($startDate, $interval, $endDate);
        $batiment = new Batiment();
        $batiment->setNom("Batiment C");
        $batiment->setAdresse("15, rue pascal");
        $batiment->setNbEtages(4);
        $manager->persist($batiment);

        $salle = new Salle();
        $salle->setNom("C201");
        $salle->setRadiateur(10);
        $salle->setEtage(2);
        $salle->setFenetre(10);
        $salle->setBatiment($batiment);
        $manager->persist($salle);
        // Pour chaque date et chaque capteur, générer une valeur
        foreach ($period as $date) {
            foreach ($types as $type) {
                $valeurCapteur = new ValeurCapteur();
                $valeurCapteur->setSa($sa);
                $valeurCapteur->setType($type);
                $valeurCapteur->setDateAjout($date);
                $valeurCapteur->setSalle($salle);

                // Générer une valeur aléatoire pour chaque type de capteur
                switch ($type) {
                    case TypeCapteur::TEMPERATURE:
                        // Température entre -10°C et 35°C
                        $lastVal[0]=$faker->randomFloat(2, $lastVal[0], $lastVal[0]+2);
                        if ($lastVal[0]>35)$lastVal[0]=35;
                        $valeurCapteur->setValeur($lastVal[0]);
                        break;
                    case TypeCapteur::HUMIDITE:
                        // Humidité entre 30% et 90%
                        $lastVal[1]=$faker->randomFloat(2, $lastVal[1], $lastVal[1]);
                        if ($lastVal[1]>90)$lastVal[0]=90;
                        $valeurCapteur->setValeur($lastVal[1]);
                        break;
                    case TypeCapteur::CO2:
                        // CO2 entre 300 et 2000 ppm
                        $lastVal[2]=$faker->randomFloat(2, $lastVal[2], $lastVal[2]+40);
                        if ($lastVal[2]>1200)$lastVal[0]=1200;
                        $valeurCapteur->setValeur($lastVal[2]);
                        break;
                }
                // Associer la valeur à la date et persister
                $manager->persist($valeurCapteur);
            }
        }

        $manager->persist($sa);
        // Enregistrer toutes les entités en base
        $manager->flush();
    }
}
