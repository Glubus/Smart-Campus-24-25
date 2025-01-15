<?php

namespace App\Tests\Controller;

use App\Entity\DetailPlan;
use App\Entity\Plan;
use App\Entity\Utilisateur;
use App\Repository\DetailPlanRepository;
use App\Repository\PlanRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DetailPlanControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void{
        $this->client = static::createClient();

        $plan = new Plan();
        $plan->setDate(new \DateTime());
        $plan->setNom('mock-plan');

        $planRepo = $this->createMock(PlanRepository::class);
        $planRepo->method('findOneBy')->with(['nom'=>'mock-plan'])->willReturn($plan);
    }

    public function testList(): void
    {
        // Step 1: Request the 'detail' page for a plan
        $crawler = $this->client->request('GET', '/plan/mock-plan/detail');
        $this->assertResponseIsSuccessful();

        // Assert the form exists
        $this->assertSelectorExists('form[name="filter_form"]');
        $this->assertSelectorExists('input[placeholder="Rechercher par nom de salle"]');

        // Assert the page contains salle items
        $this->assertSelectorExists('.block-salle'); // Selector for room blocks
    }

    public function testAjouter(): void
    {
        // Step 1: Request the 'attribuer' page with test parameters
        $crawler = $this->client->request('GET', '/plan/mock-plan/attribuer?salle=1&sa_id=1');
        $this->assertResponseIsSuccessful();

        // Assert the form exists
        $this->assertSelectorExists('form[name="association_sa_salle"]');

        // Step 2: Fill and submit the form
        $form = $crawler->selectButton('Submit')->form();
        $form['association_sa_salle[sa]'] = 'mock-sa';
        $form['association_sa_salle[salle]'] = 1;

        $this->client->submit($form);

        // Assert redirection after successful submission
        $this->assertResponseRedirects('/plan/mock-plan/detail');

        // Follow the redirect and assert success message
        $crawler = $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Details mock-plan');
    }



    public function testListWithFilter(): void
    {
        $this->client = static::createClient();

        // Step 1: Access the detail page with filter parameters
        $crawler = $this->client->request('GET', '/plan/mock-plan/detail?batiment=1');

        // Submit the filter form
        $form = $crawler->selectButton('Filtrer')->form();
        $form['filter_form[nom]'] = 'Mock Salle';
        $this->client->submit($form);

        // Assert the response is successful
        $this->assertResponseIsSuccessful();

        // Assert the filtered list of salles
        $this->assertSelectorTextContains('.nom-salle', 'Mock Salle');
    }

    public function testSuppressionPage(): void
    {
        $this->client = static::createClient();

        // Visit the suppression page for a valid DetailPlan ID
        $crawler = $this->client->request('GET', '/detail_plan/1/suppression');
        $this->assertResponseIsSuccessful();

        // Assert the page contains a confirmation message and a form
        $this->assertSelectorExists('form');
    }

    public function testValidDeletion(): void
    {
        $this->client = static::createClient();

        // Mock repository to handle removal
        $mockDetailPlanRepo = $this->createMock(DetailPlanRepository::class);
        $mockDetailPlanRepo->method('delete')->with(1)->willReturn(null);

        $this->client->getContainer()->set(DetailPlanRepository::class, $mockDetailPlanRepo);

        // Step 1: Send a request to delete
        $this->client->request('POST', '/detail_plan/1/suppression');

        // Assert redirection to the detail page
        $this->assertResponseRedirects('/plan/mock-plan/detail');

        // Step 2: Follow the redirect and ensure item is no longer listed
        $crawler = $this->client->followRedirect();
        $this->assertSelectorNotExists('.detail-plan-item[data-id="1"]');
    }
}
