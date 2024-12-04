<?php

namespace App\DataFixtures;

use App\Entity\ActionLog;
use App\Entity\SA;
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


        $sa = new SA();
        $sa->setNom($faker->word);
        $sa->setDateAjout(new \DateTime());
        $manager->persist($sa);
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
        $types = [TypeCapteur::temperature, TypeCapteur::humidite, TypeCapteur::co2];

        foreach ($types as $type) {
            $capteur = new Capteur();
            $capteur->setNom("Capteur de " . ucfirst($type->value));
            $capteur->setType($type);
            $sa->addCapteur($capteur);
            $manager->persist($capteur);

            $capteurs[] = $capteur;
        }

        // Générer des valeurs pour chaque capteur sur un mois avec une fréquence toutes les 10 minutes
        $startDate = new \DateTime('2024-11-01 00:00:00');
        $endDate = new \DateTime('2024-12-01 00:00:00');
        $interval = new \DateInterval('PT10M'); // Intervalle de 10 minutes

        // On va générer des valeurs toutes les 10 minutes pendant un mois
        $period = new \DatePeriod($startDate, $interval, $endDate);

        // Pour chaque date et chaque capteur, générer une valeur
        foreach ($period as $date) {
            foreach ($capteurs as $capteur) {
                $valeurCapteur = new ValeurCapteur();
                $valeurCapteur->setCapteur($capteur);

                // Générer une valeur aléatoire pour chaque type de capteur
                switch ($capteur->getType()) {
                    case TypeCapteur::temperature:
                        // Température entre -10°C et 35°C
                        $valeurCapteur->setValeur($faker->randomFloat(2, -10, 35));
                        break;
                    case TypeCapteur::humidite:
                        // Humidité entre 30% et 90%
                        $valeurCapteur->setValeur($faker->randomFloat(2, 30, 90));
                        break;
                    case TypeCapteur::co2:
                        // CO2 entre 300 et 2000 ppm
                        $valeurCapteur->setValeur($faker->randomFloat(2, 300, 2000));
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
