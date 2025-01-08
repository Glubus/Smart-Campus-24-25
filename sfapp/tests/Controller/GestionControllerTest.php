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
     * Tests the dashboard method in the GestionController.
     */
    public function testDashboard(): void
    {
        $client = static::createClient();


        // Perform the request
        $client->request('GET', '/admin/dashboard');

        // Assert that the response status is correct
        $this->assertResponseIsSuccessful();

        // Assert content is displayed correctly
        $this->assertSelectorTextContains('h1', 'Tableau de Bord - Gestion Écologique');
        $this->assertSelectorExists('#co2Chart'); // Example: Verify chart containers exist
        $this->assertSelectorExists('#tempChart');
        $this->assertSelectorExists('#humChart');
    }

    /**
     * Tests the dashboard method with a period of 1 day.
     */
    public function testDashboardPeriodOneDay(): void
    {
        $client = static::createClient();

        // Perform the request with the specified period
        $client->request('GET', '/admin/dashboard/1');

        // Assert that the response status is successful
        $this->assertResponseIsSuccessful();

    }

    /**
     * Tests the dashboard method with a period of 30 days.
     */
    public function testDashboardPeriodThirtyDays(): void
    {
        $client = static::createClient();

        // Perform the request with the specified period
        $client->request('GET', '/admin/dashboard/30');

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();

    }

    /**
     * Tests the supprimer method when no items are selected.
     */
    public function testSupprimerWithoutSelectedItems(): void
    {
        $client = static::createClient();

        // Perform the request without providing IDs
        $client->request('POST', '/admin/technicien/supprimer', []);

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();

        // Assert that the page contains a form for deleting technicians
        $this->assertSelectorExists('form input[name="inputString"]');
    }

    /**
     * Tests the supprimer method with invalid confirmation input.
     */
    public function testSupprimerWithInvalidConfirmation(): void
    {
        $client = static::createClient();

        // Perform the request with selected IDs
        $crawler = $client->request('POST', '/admin/technicien/supprimer', [
            'selected' => [1, 2],
        ]);

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();

        // Submit the form with an invalid confirmation string
        $form = $crawler->selectButton('Supprimer')->form();
        $form['inputString'] = 'INVALID';
        $client->submit($form);

        // Assert that the error message is displayed
        $this->assertSelectorTextContains('.flash-error', 'La saisie est incorrecte.');
    }

    /**
     * Tests the supprimer method with valid confirmation input.
     */
    public function testSupprimerWithValidConfirmation(): void
    {
        $client = static::createClient();

        // Perform the request with selected IDs
        $crawler = $client->request('POST', '/admin/technicien/supprimer', [
            'selected' => [1, 2],
        ]);

        // Submit the form with a valid confirmation string
        $form = $crawler->selectButton('Supprimer')->form();
        $form['inputString'] = 'CONFIRMER';
        $client->submit($form);

        // Assert that the response redirects to the batiment list page
        $this->assertResponseRedirects('/admin/technicien');
    }

    /**
     * Tests the gestion_technicien method in the GestionController.
     */
    public function testGestionTechnicienReturnsTechniciansList(): void
    {
        $client = static::createClient();
        $user = new Utilisateur();
        $user->setNom("technicien1");
        $user->setAdresse("template");
        $user->setPrenom("pascal");
        $user->setEmail("superman");
        $user->setUsername("AZXSQAZAZ1");
        $user->setPassword("template");
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($user);
        $entityManager->flush($user);

        // Perform the request
        $client->request('GET', '/admin/technicien');

        // Assert the response is successful
        $this->assertResponseIsSuccessful();

        // Check if technicians are displayed correctly
        $this->assertAnySelectorTextContains('.technicienNom', 'technicien1');
        $entityManager->remove($user);
        $entityManager->flush($user);
    }
    /**
     * Tests the submission and validation of the ajoutBatimentType form in the GestionController.
     */
    public function testGestionFormSubmissionBatiment(): void
    {
        $client = static::createClient();

        $client->request('GET', '/gestion');

        $this->assertResponseIsSuccessful();

        $crawler = $client->request('POST', '/gestion', [
            'ajout_batiment' => [
                'nom' => 'BatimentTest',
                'adresse' => '123, Rue de Test',
                'nbEtages' => 4,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.flash-success', 'Bâtiment ajouté avec succès !');
    }

    /**
     * Tests the submission and validation of the ajoutSalleType form in the GestionController.
     */
    public function testGestionFormSubmissionSalle(): void
    {
        $client = static::createClient();

        $client->request('GET', '/gestion');

        $this->assertResponseIsSuccessful();

        $crawler = $client->request('POST', '/gestion', [
            'ajout_salle' => [
                'nom' => 'SalleTest',
                'fenetre' => 2,
                'radiateur' => 1,
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.flash-success', 'Salle ajoutée avec succès !');
    }

    /**
     * Tests the rendering of all forms in the GestionController.
     */
    public function testGestionFormRendering(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/gestion');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="ajout_batiment"]');
        $this->assertSelectorExists('form[name="ajout_salle"]');
        $this->assertSelectorExists('form[name="association_sa_salle"]');
        $this->assertSelectorExists('form[name="ajout_sa"]');
    }
}