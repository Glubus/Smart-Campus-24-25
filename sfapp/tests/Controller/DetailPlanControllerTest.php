<?php

namespace App\Tests\Controller;

use App\Entity\DetailPlan;
use App\Entity\EtatInstallation;
use App\Entity\Plan;
use App\Entity\Utilisateur;
use App\Repository\BatimentRepository;
use App\Repository\DetailPlanRepository;
use App\Repository\PlanRepository;
use App\Repository\SalleRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DetailPlanControllerTest extends WebTestCase
{
    public function testListAucunResultat(): void
    {
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
        $client->loginUser($user);

        // Step 1: Request the 'detail' page for a plan
        $crawler = $client->request('GET', '/plan/planTest/detail');
        $this->assertResponseIsSuccessful();

        // Assert the form exists
        $this->assertSelectorExists('#form_nom');
        $this->assertSelectorExists('input[placeholder="Rechercher par nom de salle"]');

        // Assert the page contains salle items
        $this->assertSelectorExists('.aucunResultat'); // Selector for room blocks
    }

    public function testListBatimentD()
    {
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
        $client->loginUser($user);

        $repo = static::getContainer()->get(BatimentRepository::class);
        $bat = $repo->findOneBy(['nom' => 'Batiment D']);

        // Step 1: Request the 'detail' page for a plan
        $crawler = $client->request('GET', '/plan/planTest/detail?batiment='.$bat->getId());
        $this->assertResponseIsSuccessful();

        // Assert the page contains salle items
        $this->assertSelectorExists('.block-salle'); // Selector for room blocks
        $this->assertAnySelectorTextContains('.nom-salle', 'D001');
        $this->assertAnySelectorTextContains('.SAattribue', 'ESP-030');
    }

    public function testPageAttribution(): void
    {
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
        $client->loginUser($user);

        $repo = static::getContainer()->get(SalleRepository::class);
        $s = $repo->findOneBy(['nom' => 'D001']);

        // Step 1: Request the 'attribuer' page with test parameters
        $crawler = $client->request('GET', '/plan/planTest/attribuer?salle='.$s->getId());
        $this->assertResponseIsSuccessful();

        // Assert the form exists
        $this->assertSelectorExists('#association_sa_salle');
        $this->assertAnySelectorTextContains('h1', 'installation');
    }

        public function testPageDeinstallation(): void
        {
            $client = static::createClient();
            $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
            $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
            $client->loginUser($user);

            $repo = static::getContainer()->get(DetailPlanRepository::class);
            $dp = $repo->findOneBy([]);

            // Visit the suppression page for a valid DetailPlan ID
            $crawler = $client->request('GET', '/detail_plan/'.$dp->getId().'/suppression');
            $this->assertResponseIsSuccessful();

            // Assert the page contains a confirmation message and a form
            $this->assertSelectorExists('form');
            $this->assertAnySelectorTextContains('h1', 'deinstallation');
        }

        public function testValidationPage(): void
        {
            $client = static::createClient();
            $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
            $user = $utilisateurRepository->findOneBy(['username' => 'jdupon']);
            $client->loginUser($user);

            $repo = static::getContainer()->get(DetailPlanRepository::class);
            $dp = $repo->findOneBy([]);
            $dp->setEtatSA(EtatInstallation::INSTALLATION);

            // Visit the suppression page for a valid DetailPlan ID
            $crawler = $client->request('GET', '/detail_plan/' . $dp->getId() . '/valider');
            $this->assertResponseIsSuccessful();

            // Assert the page contains a confirmation message and a form
            $this->assertSelectorExists('form');
            $this->assertAnySelectorTextContains('h1', 'installation');
        }
}
