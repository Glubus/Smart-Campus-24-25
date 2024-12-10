<?php

namespace App\DataFixtures;

use App\Entity\SA;
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
        $manager->persist($sa);
        // Créer un tableau pour stocker les capteurs
        $capteurs = [];

        // Création des capteurs de type température, humidité, CO2
        $types = [TypeCapteur::TEMPERATURE, TypeCapteur::HUMIDITE, TypeCapteur::CO2];

        foreach ($types as $type) {
            $capteur = new Capteur();
            $capteur->setNom("Capteur de " . ucfirst($type->value));
            $capteur->setType($type);
            $sa->addCapteur($capteur);
            $manager->persist($capteur);
        }

        // Générer des valeurs pour chaque capteur sur un mois avec une fréquence toutes les 10 minutes
        $startDate = new \DateTime('2024-09-01 00:00:00');
        $endDate = new \DateTime('2024-12-31 00:00:00');
        $interval = new \DateInterval('PT10M'); // Intervalle de 10 minutes

        // On va générer des valeurs toutes les 10 minutes pendant un mois
        $period = new \DatePeriod($startDate, $interval, $endDate);

        // Pour chaque date et chaque capteur, générer une valeur
        foreach ($period as $date) {
            foreach ($types as $type) {
                $valeurCapteur = new ValeurCapteur();
                $valeurCapteur->setSa($sa);
                $valeurCapteur->setNom($type);
                $valeurCapteur->setDate($date);
                $valeurCapteur->setDescription($faker->text);
                $valeurCapteur->setLocalisation($faker->bothify("D###"));
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

        // Enregistrer toutes les entités en base
        $manager->flush();
    }
}
