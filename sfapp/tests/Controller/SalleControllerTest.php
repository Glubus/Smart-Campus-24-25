<?php

namespace App\Tests\Controller;

use App\Entity\Salle;
use App\Repository\DetailPlanRepository;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SalleControllerTest extends WebTestCase
{
    /**
     * Tests that the saAttribues method returns the salle and associated SAs when given a valid salle ID.
     */
    public function testSaAttribues_ReturnsSalleAndSAs_WhenValidSalleIdProvided(): void
    {
        $client = static::createClient();

        $salleMock = $this->createMock(Salle::class);
        $salleMock->method('getId')->willReturn(1);
        $salleMock->method('getNom')->willReturn('Salle A');

        $detailPlanMock = $this->createMock(DetailPlanRepository::class);
        $detailPlanMock->method('findBy')->willReturn([$this->createMock(DetailPlan::class)]);

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $salleRepositoryMock->method('find')->willReturn($salleMock);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            DetailPlanRepository::class => $detailPlanMock,
        ]);

        $crawler = $client->request('GET', '/salle/saAttribues/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.salle-sa-list');
        $this->assertSelectorTextContains('.salle-name', 'Salle A');
    }

    /**
     * Tests that the saAttribues method throws a 404 error when the salle ID is invalid.
     */
    public function testSaAttribues_ThrowsNotFound_WhenInvalidSalleIdProvided(): void
    {
        $client = static::createClient();

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $salleRepositoryMock->method('find')->willReturn(null);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $client->request('GET', '/salle/saAttribues/999');

        $this->assertResponseStatusCodeSame(404);
    }
    /**
     * Tests the index method of SalleController.
     */
    public function testIndex_ReturnsSallesView_WhenFormNotSubmitted(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $detailPlanRepositoryMock = $this->createMock(DetailPlanRepository::class);

        $salleRepositoryMock
            ->method('findAll')
            ->willReturn([
                (new Salle())->setId(1)->setNom('Salle A'),
                (new Salle())->setId(2)->setNom('Salle B'),
            ]);

        $detailPlanRepositoryMock
            ->method('findAll')
            ->willReturn([]);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            DetailPlanRepository::class => $detailPlanRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); // Form exists
        $this->assertSelectorExists('#salle-list'); // Salle list container
        $this->assertSelectorContains('.salle-item', 'Salle A');
        $this->assertSelectorContains('.salle-item', 'Salle B');
    }

    public function testIndex_ReturnsSallesView_WhenFormSubmitted_AndSearchResultsFound(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $detailPlanRepositoryMock = $this->createMock(DetailPlanRepository::class);

        $salleRepositoryMock
            ->method('findAll')
            ->willReturn([
                (new Salle())->setId(1)->setNom('Salle A'),
                (new Salle())->setId(2)->setNom('Salle B'),
            ]);

        $detailPlanRepositoryMock
            ->method('findAll')
            ->willReturn([]);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            DetailPlanRepository::class => $detailPlanRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle');

        $form = $crawler->filter('form')->form([
            'recherche_salle_type[salleNom]' => 'Salle A',
        ]);

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.salle-item'); // Must contain matching salles
        $this->assertSelectorTextContains('.salle-item', 'Salle A');
        $this->assertSelectorNotContains('.salle-item', 'Salle B');
    }

    public function testIndex_ReturnsNotFound_WhenNoSallesFound(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $detailPlanRepositoryMock = $this->createMock(DetailPlanRepository::class);

        $salleRepositoryMock
            ->method('findAll')
            ->willReturn([]);

        $detailPlanRepositoryMock
            ->method('findAll')
            ->willReturn([]);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            DetailPlanRepository::class => $detailPlanRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); // Form exists
        $this->assertSelectorExists('#notfound-message'); // Not found container
    }

    private function mockRepositories($client, array $repositories)
    {
        $container = $client->getContainer();

        foreach ($repositories as $class => $mock) {
            $container->set($class, $mock);
        }
    }
    /**
     * Tests that the infos method shows salle information view when valid salle ID is provided.
     */
    public function testInfos_ReturnsSalleInfoView_WhenValidSalleIdProvided(): void
    {
        $client = static::createClient();

        $salleMock = $this->createMock(Salle::class);
        $salleMock->method('getOnlySa')->willReturn(1);
        $salleMock->method('getValeurCapteurs')->willReturn(['data' => []]);

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $salleRepositoryMock->method('find')->willReturn($salleMock);

        $valeurCapteurRepositoryMock = $this->createMock(ValeurCapteurRepository::class);
        $valeurCapteurRepositoryMock->method('findDataForSalle2')->willReturn([]);

        $detailPlanRepositoryMock = $this->createMock(DetailPlanRepository::class);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            ValeurCapteurRepository::class => $valeurCapteurRepositoryMock,
            DetailPlanRepository::class => $detailPlanRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.salle-info');
    }

    /**
     * Tests returning infos sans capteur view when getOnlySa() is -1.
     */
    public function testInfos_ReturnsInfosSansCapteur_WhenOnlySaIsNegative(): void
    {
        $client = static::createClient();

        $salleMock = $this->createMock(Salle::class);
        $salleMock->method('getOnlySa')->willReturn(-1);

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $salleRepositoryMock->method('find')->willReturn($salleMock);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.infos-sans-capteur');
    }

    /**
     * Tests that the infos method throws a not found error when an invalid salle ID is provided.
     */
    public function testInfos_ThrowsNotFoundError_WhenSalleIdInvalid(): void
    {
        $client = static::createClient();

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $salleRepositoryMock->method('find')->willReturn(null);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $client->request('GET', '/salle/9999');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Tests that infosUser returns user view when a valid salle ID is provided.
     */
    public function testInfosUser_ReturnsUserView_WhenValidSalleIdProvided(): void
    {
        $client = static::createClient();

        $salleMock = $this->createMock(Salle::class);
        $salleMock->method('getNom')->willReturn('Salle Test');

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $salleRepositoryMock->method('find')->willReturn($salleMock);
        $salleRepositoryMock->method('requestSalle')->willReturn($this->createMockResponse([
            ['nom' => 'temp', 'valeur' => 22.5],
            ['nom' => 'hum', 'valeur' => 45.7],
            ['nom' => 'co2', 'valeur' => 500]
        ]));

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/user/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.user-infos');
        $this->assertSelectorContains('.user-infos .salle-name', 'Salle Test');
    }

    /**
     * Tests that infosUser returns 404 when an invalid ID is provided.
     */
    public function testInfosUser_ReturnsNotFound_WhenInvalidSalleIdProvided(): void
    {
        $client = static::createClient();

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $salleRepositoryMock->method('find')->willReturn(null);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $client->request('GET', '/salle/user/9999');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * Tests that infosUser correctly parses temperature, humidity, and CO2 values.
     */
    public function testInfosUser_CorrectlyParsesSensorValues(): void
    {
        $client = static::createClient();

        $salleMock = $this->createMock(Salle::class);
        $salleMock->method('getNom')->willReturn('Salle Test');

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $salleRepositoryMock->method('find')->willReturn($salleMock);
        $salleRepositoryMock->method('requestSalle')->willReturn($this->createMockResponse([
            ['nom' => 'temp', 'valeur' => 24.3],
            ['nom' => 'hum', 'valeur' => 60.7],
            ['nom' => 'co2', 'valeur' => 700]
        ]));

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/user/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.sensor-temp', '24.3');
        $this->assertSelectorTextContains('.sensor-humidity', '60.7');
        $this->assertSelectorTextContains('.sensor-co2', '700');
    }

    /**
     * Tests that the ajouter method displays the form when accessed.
     */
    public function testAjouter_DisplaysForm_WhenAccessed(): void
    {
        $client = static::createClient();
        $batimentRepositoryMock = $this->createMock(BatimentRepository::class);
        $batimentRepositoryMock->method('findAll')->willReturn([]);

        $this->mockRepositories($client, [
            BatimentRepository::class => $batimentRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/ajouter');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); // Form is displayed
    }

    /**
     * Tests that the ajouter method redirects to the salle list when a new salle is successfully added.
     */
    public function testAjouter_RedirectsToList_WhenSalleAddedSuccessfully(): void
    {
        $client = static::createClient();
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $batimentRepositoryMock = $this->createMock(BatimentRepository::class);

        $salleRepositoryMock->method('findOneBy')->willReturn(null); // Salle does not already exist

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            BatimentRepository::class => $batimentRepositoryMock,
            EntityManagerInterface::class => $entityManagerMock,
        ]);

        $crawler = $client->request('GET', '/salle/ajouter');
        $form = $crawler->filter('form')->form();
        $form['salle[nom]'] = 'New Salle';
        $form['salle[batiment]'] = 1;

        $client->submit($form);

        $this->assertResponseRedirects('/salle');
    }

    /**
     * Tests that the ajouter method shows an error message when a salle with the same name already exists.
     */
    public function testAjouter_ShowsError_WhenSalleAlreadyExists(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $batimentRepositoryMock = $this->createMock(BatimentRepository::class);

        $existingSalle = new Salle();
        $existingSalle->setNom('Existing Salle');
        $salleRepositoryMock->method('findOneBy')->willReturn($existingSalle);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            BatimentRepository::class => $batimentRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/ajouter');
        $form = $crawler->filter('form')->form();
        $form['salle[nom]'] = 'Existing Salle';
        $form['salle[batiment]'] = 1;

        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful(); // The request is handled successfully
        $this->assertSelectorExists('.flash-error'); // Error message is displayed
        $this->assertSelectorContains('.flash-error', 'Cette salle existe déjà');
    }

    private function createMockResponse(array $data)
    {
        $responseMock = $this->createMock(Response::class);
        $responseMock->method('getContent')->willReturn(json_encode($data));
        return $responseMock;
    }
    /**
     * Tests that the modifier method displays the form when accessed.
     */
    public function testModifier_DisplaysForm_WhenAccessed(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);

        $salleMock = new Salle();
        $salleMock->setId(1)->setNom('Existing Salle');
        $salleRepositoryMock->method('find')->willReturn($salleMock);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/modifier/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); // Ensure form is displayed
    }

    /**
     * Tests that the modifier method redirects to the salle list when a salle is successfully modified.
     */
    public function testModifier_RedirectsToList_WhenSalleModifiedSuccessfully(): void
    {
        $client = static::createClient();
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $salleRepositoryMock = $this->createMock(SalleRepository::class);

        $salleMock = new Salle();
        $salleMock->setId(1)->setNom('Modified Salle');
        $salleRepositoryMock->method('find')->willReturn($salleMock);
        $salleRepositoryMock->method('findBy')->willReturn([]);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            EntityManagerInterface::class => $entityManagerMock,
        ]);

        $crawler = $client->request('GET', '/salle/modifier/1');
        $form = $crawler->filter('form')->form();
        $form['salle[nom]'] = 'Modified Salle';

        $client->submit($form);

        $this->assertResponseRedirects('/salle');
    }

    /**
     * Tests that the modifier method shows an error message when a salle with the same name already exists.
     */
    public function testModifier_ShowsError_WhenSalleAlreadyExists(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);

        $salleMock = new Salle();
        $salleMock->setId(1)->setNom('Existing Salle');
        $salleRepositoryMock->method('find')->willReturn($salleMock);
        $salleRepositoryMock->method('findBy')->willReturn([$salleMock]); // Simulate existing salle conflict

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/modifier/1');
        $form = $crawler->filter('form')->form();
        $form['salle[nom]'] = 'Existing Salle';

        $crawler = $client->submit($form);

        $this->assertResponseIsSuccessful(); // The request is handled successfully
        $this->assertSelectorExists('.flash-error'); // Error message is displayed
        $this->assertSelectorContains('.flash-error', 'Cette salle existe déjà');
    }
    /**
     * Tests that salles linked to a given batiment are successfully deleted when confirmation is correct.
     */
    public function testSupprimerSallesLiees_DeletesSalles_WhenValidBatimentAndConfirmation(): void
    {
        $client = static::createClient();

        $batimentMock = $this->createMock(Batiment::class);
        $batimentMock->method('getNom')->willReturn('Test Batiment');

        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $batimentRepositoryMock = $this->createMock(BatimentRepository::class);
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $batimentRepositoryMock->method('find')->willReturn($batimentMock);
        $salleRepositoryMock->method('findBy')->willReturn([
            (new Salle())->setId(1)->setNom('Salle A'),
            (new Salle())->setId(2)->setNom('Salle B'),
        ]);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            BatimentRepository::class => $batimentRepositoryMock,
            EntityManagerInterface::class => $entityManagerMock,
        ]);

        $crawler = $client->request('GET', '/salle/supprimer-liees/1');
        $form = $crawler->filter('form')->form([
            'inputString' => 'Test Batiment',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/app_batiment_liste');
        $client->followRedirect();
        $this->assertSelectorExists('.flash-success');
        $this->assertSelectorContains('.flash-success', 'Toutes les salles associées au bâtiment ont été supprimées.');
    }

    /**
     * Tests that the supprimerSelection method displays the form when no salles are selected.
     */
    public function testSupprimerSelection_DisplaysForm_WhenNoSallesSelected(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
        ]);

        $crawler = $client->request('POST', '/salle/supprimer-selection');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form'); // Ensure form is displayed
    }

    /**
     * Tests that the supprimerSelection method removes selected salles when confirmation is correct.
     */
    public function testSupprimerSelection_RemovesSelectedSalles_WhenConfirmed(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $salle1 = (new Salle())->setId(1)->setNom('Salle A');
        $salle2 = (new Salle())->setId(2)->setNom('Salle B');
        $salleRepositoryMock->method('find')->willReturnMap([
            [1, $salle1],
            [2, $salle2],
        ]);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            EntityManagerInterface::class => $entityManagerMock,
        ]);

        $crawler = $client->request('POST', '/salle/supprimer-selection', [
            'selected_salles' => [1, 2],
        ]);

        $form = $crawler->filter('form')->form([
            'inputString' => 'CONFIRMER',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/app_salle');
    }

    /**
     * Tests that the supprimerSelection method shows an error message when the confirmation input is incorrect.
     */
    public function testSupprimerSelection_ShowsError_WhenIncorrectConfirmation(): void
    {
        $client = static::createClient();
        $salleRepositoryMock = $this->createMock(SalleRepository::class);
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $salle1 = (new Salle())->setId(1)->setNom('Salle A');
        $salle2 = (new Salle())->setId(2)->setNom('Salle B');
        $salleRepositoryMock->method('find')->willReturnMap([
            [1, $salle1],
            [2, $salle2],
        ]);

        $this->mockRepositories($client, [
            SalleRepository::class => $salleRepositoryMock,
            EntityManagerInterface::class => $entityManagerMock,
        ]);

        $crawler = $client->request('POST', '/salle/supprimer-selection', [
            'selected_salles' => [1, 2],
        ]);

        $form = $crawler->filter('form')->form([
            'inputString' => 'WRONG',
        ]);

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.flash-error'); // Error message is displayed
        $this->assertSelectorContains('.flash-error', 'La saisie est incorrect.');
    }

    /**
     * Tests that an error is shown when trying to delete salles of an invalid batiment.
     */
    public function testSupprimerSallesLiees_Fails_WhenInvalidBatiment(): void
    {
        $client = static::createClient();

        $batimentRepositoryMock = $this->createMock(BatimentRepository::class);
        $batimentRepositoryMock->method('find')->willReturn(null);

        $this->mockRepositories($client, [
            BatimentRepository::class => $batimentRepositoryMock,
        ]);

        $client->request('GET', '/salle/supprimer-liees/999');

        $this->assertResponseRedirects('/app_batiment_liste');
        $client->followRedirect();
        $this->assertSelectorExists('.flash-error');
        $this->assertSelectorContains('.flash-error', 'Le bâtiment spécifié n\'existe pas.');
    }

    /**
     * Tests that an error message is shown when the input confirmation is incorrect.
     */
    public function testSupprimerSallesLiees_ShowsError_WhenIncorrectConfirmation(): void
    {
        $client = static::createClient();

        $batimentMock = $this->createMock(Batiment::class);
        $batimentMock->method('getNom')->willReturn('Test Batiment');

        $batimentRepositoryMock = $this->createMock(BatimentRepository::class);
        $batimentRepositoryMock->method('find')->willReturn($batimentMock);

        $this->mockRepositories($client, [
            BatimentRepository::class => $batimentRepositoryMock,
        ]);

        $crawler = $client->request('GET', '/salle/supprimer-liees/1');
        $form = $crawler->filter('form')->form([
            'inputString' => 'Incorrect Confirmation',
        ]);

        $client->submit($form);

        $this->assertResponseIsSuccessful(); // The page reloads without errors
        $this->assertSelectorExists('.flash-error');
        $this->assertSelectorContains('.flash-error', 'La saisie est incorrecte. Opération annulée.');
    }
}