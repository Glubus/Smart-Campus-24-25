<?php

namespace App\Tests\Controller;

use App\Entity\Batiment;
use App\Entity\DetailPlan;
use App\Entity\Etage;
use App\Entity\Plan;
use App\Entity\SA;
use App\Entity\Salle;
use App\Repository\DetailPlanRepository;
use App\Repository\SalleRepository;
use App\Repository\UtilisateurRepository;
use BotMan\BotMan\Users\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SalleControllerTest extends WebTestCase
{
    public function testListeSalleUser_RedirectionSansConnexion(): void
    {
        $client = static::createClient();
        $client->request('GET', '/salle');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testListeSalleUser_AccesSansConnexion(): void
    {
        $client = static::createClient();

        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']); // ou l'identifiant correct
        $client->loginUser($user);

        $crawler = $client->request('GET', '/salle');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testListeSalleUser_ContientSalleSansSa(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $salle = new Salle();
        $salle->setNom('Salle 1');

        $batiment = new Batiment();
        $batiment->setNom('Batiment D');
        $batiment->setAdresse('Adresse 1');
        $batiment->setNbEtages(2);

        $entityManager->persist($batiment);

        $etage = new Etage();
        $etage->setNom('Etage 1');
        $etage->setNiveau(1);
        $etage->setBatiment($batiment);

        $entityManager->persist($etage);

        $salle->setEtage($etage);

        $entityManager->persist($salle);
        $entityManager->flush();

        $crawler = $client->request('GET', '/salle/user');
        $this->assertAnySelectorTextContains('.salleName', 'Salle 1');

        $entityManager->remove($salle);
        $entityManager->remove($etage);
        $entityManager->remove($batiment);
        $entityManager->flush();
    }

    public function testListeSalleUser_ContientSalleAvecSa(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $batiment = new Batiment();
        $batiment->setNom('Batiment D');
        $batiment->setAdresse('Adresse 1');
        $batiment->setNbEtages(2);
        $entityManager->persist($batiment);

        $etage = new Etage();
        $etage->setNom('Etage 1');
        $etage->setNiveau(1);
        $etage->setBatiment($batiment);
        $entityManager->persist($etage);

        $salle = new Salle();
        $salle->setNom('Salle 1');
        $salle->setEtage($etage);
        $entityManager->persist($salle);

        $sa = new SA();
        $sa->setNom('SAtest');
        $entityManager->persist($sa);

        $plan = new Plan();
        $plan->setNom('plan 1');
        $plan->setDate(new \DateTime('2025-01-01'));
        $entityManager->persist($plan);

        $detailPlan = new DetailPlan();
        $detailPlan->setSalle($salle);
        $detailPlan->setSA($sa);
        $detailPlan->setDateAjout(new \DateTime('2025-01-02'));
        $detailPlan->setPlan($plan);
        $entityManager->persist($detailPlan);

        $entityManager->flush();

        $crawler = $client->request('GET', '/salle/user');
        $this->assertAnySelectorTextContains('.salleName', 'Salle 1');
        $this->assertAnySelectorTextContains('.location', 'Batiment D - Etage Etage 1');

        $entityManager->remove($detailPlan);
        $entityManager->remove($salle);
        $entityManager->remove($etage);
        $entityManager->remove($batiment);
        $entityManager->remove($plan);
        $entityManager->flush();
    }

    public function testListeSalleUser_RedirigeVersInfos(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $batiment = new Batiment();
        $batiment->setNom('Batiment D');
        $batiment->setAdresse('Adresse 1');
        $batiment->setNbEtages(2);
        $entityManager->persist($batiment);

        $etage = new Etage();
        $etage->setNom('Etage 1');
        $etage->setNiveau(1);
        $etage->setBatiment($batiment);
        $entityManager->persist($etage);

        $salle = new Salle();
        $salle->setNom('Salle 1');
        $salle->setEtage($etage);
        $entityManager->persist($salle);

        $entityManager->flush();
        $crawler = $client->request('GET', '/salle/user');
        $client->followRedirect();
    }
}