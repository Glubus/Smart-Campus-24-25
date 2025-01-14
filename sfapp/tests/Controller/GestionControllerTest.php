<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\ApiWrapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Cache\CacheInterface;

class GestionControllerTest extends WebTestCase
{
    /**
     * Tests the diagnosticSalle method with valid data.
     */
    public function testDiagnosticSalleValide(): void
    {
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']); // ou l'identifiant correct
        $client->loginUser($user);
        $client->request('GET', "/outils/diagnostic/Batiment D/D206");
        $this->assertResponseIsSuccessful();
        // Assert response and data rendering
        $this->assertSelectorTextContains('h1', 'Outil de Diagnostic - Salle D206');
        $this->assertSelectorExists('#co2Chart');
        $this->assertSelectorExists('#tempChart');
        $this->assertSelectorExists('#humChart');
        $this->assertSelectorTextContains('.add-comment-section', 'Ajouter un commentaire');
        $this->assertSelectorExists('select#timePeriod');
        $this->assertSelectorTextContains('section.statistiques-globales h2', 'Statistiques Globales');
        $this->assertSelectorTextContains('div.card-diagnostic.valeurCard.co2','CO₂ de la salle (ppm)');
        $this->assertSelectorTextContains('div.card-diagnostic.valeurCard.temp','Température de la salle (°C)');
        $this->assertSelectorTextContains('div.card-diagnostic.valeurCard.humidite','Humidité de la salle (%)');

    }

    /**
     * Tests the diagnosticSalle method with an invalid salle name.
     */
    public function testDiagnosticSalleInvalide(): void
    {
        $client = static::createClient();


        // Perform the request with an invalid salle
        $client->request('GET', "/outils/diagnostic/Batiment D/D707");

        // Assert that a 404 or appropriate error response is returned
        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Tests the diagnosticSalle method with an invalid batiment name.
     */
    public function testDiagnosticSalleBatimentInvalide(): void
    {
        $client = static::createClient();

        $invalidBatimentName = 'Batiment C';
        $salleName = 'D303';

        // Perform the request with an invalid batiment
        $client->request('GET', "/outils/diagnostic/$invalidBatimentName/$salleName");

        // Assert that a 404 or appropriate error response is returned
        $this->assertResponseStatusCodeSame(404);
    }
}