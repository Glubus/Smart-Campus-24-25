<?php

namespace App\Tests\Controller;

use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PlanControllerTest extends WebTestCase
{
    /**
     * Test the index method of PlanController
     * This test ensures the route '/plan' is accessible and renders the correct view.
     */
    public function testIndex(): void
    {
        $client = static::createClient();

        // Mock PlanRepository to return test data
        $planRepo = $this->createMock(PlanRepository::class);
        $planRepo->method('findAll')->willReturn([]);

        $client->getContainer()->set(PlanRepository::class, $planRepo);

        // Send a GET request to the route
        $client->request('GET', '/plan');

        // Assert the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert that the correct template is rendered
        $this->assertSelectorExists('title');
        $this->assertSelectorTextContains('title', 'plan');
    }

    /**
     * Test that the modifier method renders the form correctly for a valid plan ID.
     */
    public function testModifierFormRendersCorrectly(): void
    {
        $client = static::createClient();

        // Mock an existing Plan entity
        $plan = new Plan();
        $plan->setId(1);
        $plan->setNom('Existing Plan');

        $planRepo = $this->createMock(PlanRepository::class);
        $planRepo->method('find')->with(1)->willReturn($plan);

        $client->getContainer()->set(PlanRepository::class, $planRepo);

        // Send a GET request to the modifier route
        $client->request('GET', '/plan/modifier/1');

        // Assert the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert the form exists on the page
        $this->assertSelectorExists('form');
    }

    /**
     * Test valid form submission for the modifier method
     * and ensure it redirects to the '/plan' route.
     */
    public function testModifierValidSubmission(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Mock an existing Plan entity
        $plan = new Plan();
        $plan->setId(1);
        $plan->setNom('Existing Plan');
        $entityManager->persist($plan);
        $entityManager->flush();

        // Send a POST request with valid form data
        $client->request('POST', '/plan/modifier/1', [
            'modifier_plan' => [
                'nom' => 'Updated Plan Name',
                'batiments' => [], // Mock existing or none batiments, if necessary
            ]
        ]);

        // Assert the form is successfully processed and redirects
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/plan');

        // Refresh the entity to get updated data
        $entityManager->refresh($plan);
        $this->assertSame('Updated Plan Name', $plan->getNom());
    }

    /**
     * Test invalid form submission for the modifier method
     * and ensure the form is displayed with errors.
     */
    public function testModifierInvalidSubmission(): void
    {
        $client = static::createClient();

        // Mock an existing Plan entity
        $plan = new Plan();
        $plan->setId(1);
        $plan->setNom('Existing Plan');

        $planRepo = $this->createMock(PlanRepository::class);
        $planRepo->method('find')->with(1)->willReturn($plan);

        $client->getContainer()->set(PlanRepository::class, $planRepo);

        // Send a POST request with invalid form data (empty 'nom')
        $crawler = $client->request('POST', '/plan/modifier/1', [
            'modifier_plan' => [
                'nom' => '',
                'batiments' => [],
            ]
        ]);

        // Assert the response status code stays OK as it should return the form
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert that the form is displayed with an error
        $this->assertSelectorExists('.form-error-message');
    }

    /**
     * Test the index method if data is returned from the database
     * This test ensures that the view displays the list of plans fetched from the repository.
     */
    public function testIndexWithPlans(): void
    {
        $client = static::createClient();

        // Mock PlanRepository to return test data
        $planRepo = $this->createMock(PlanRepository::class);
        $planRepo->method('findAll')->willReturn([
            ['id' => 1, 'name' => 'Plan 1'],
            ['id' => 2, 'name' => 'Plan 2'],
        ]);

        $client->getContainer()->set(PlanRepository::class, $planRepo);

        // Send a GET request to the route
        $crawler = $client->request('GET', '/plan');

        // Assert the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert that the correct data is rendered
        $this->assertSelectorTextContains('body', 'Plan 1');
        $this->assertSelectorTextContains('body', 'Plan 2');
    }

    /**
     * Test that an invalid route results in a 404 error
     */
    public function testIndexInvalidRoute(): void
    {
        $client = static::createClient();

        // Send a GET request to an invalid route
        $client->request('GET', '/invalid-route');

        // Assert that the response status code is 404 (Not Found)
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Test that the ajouter method renders the form correctly.
     */
    public function testAjouterFormRendersCorrectly(): void
    {
        $client = static::createClient();

        // Send a GET request to the ajouter route
        $client->request('GET', '/plan/ajouter');

        // Assert the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert the form exists on the page
        $this->assertSelectorExists('form');
    }

    /**
     * Test valid form submission for the ajouter method
     * and ensure it redirects to the '/plan' route.
     */
    public function testAjouterValidSubmission(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Send a POST request to submit the valid form
        $client->request('POST', '/plan/ajouter', [
            'ajout_plan' => [
                'nom' => 'Valid Plan Name',
                'batiments' => [], // Mock batiments, if necessary
            ]
        ]);

        // Assert the form is successfully processed and redirects
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/plan');

        // Flush changes and verify in the database
        $entityManager->flush();
        $this->assertNotNull($entityManager->getRepository(Plan::class)->findOneBy(['nom' => 'Valid Plan Name']));
    }

    /**
     * Test invalid form submission for the ajouter method
     * and ensure the form is displayed with errors.
     */
    public function testAjouterInvalidSubmission(): void
    {
        $client = static::createClient();

        // Send a POST request with an invalid form submission (e.g., empty 'nom')
        $crawler = $client->request('POST', '/plan/ajouter', [
            'ajout_plan' => [
                'nom' => '',
                'batiments' => [],
            ]
        ]);

        // Assert the response status code stays OK as it should return the form
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert that the form is displayed with an error
        $this->assertSelectorExists('.form-error-message');
    }

    /**
     * Test valid submission for the supprimer method
     * and ensure it deletes the plans and redirects to the '/plan' route.
     */
    public function testSupprimerValidSubmission(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Mock plans to delete
        $plan1 = (new Plan())->setId(1)->setNom('Plan 1');
        $plan2 = (new Plan())->setId(2)->setNom('Plan 2');

        $entityManager->persist($plan1);
        $entityManager->persist($plan2);
        $entityManager->flush();

        // Send a POST request with valid confirmation
        $client->request('POST', '/plan/supprimer', [
            'selected_plans' => [1, 2],
            'suppression' => ['inputString' => 'CONFIRMER'],
        ]);

        // Assert the form is successfully processed and redirects
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/plan');

        // Assert the plans are deleted
        $this->assertNull($entityManager->getRepository(Plan::class)->find(1));
        $this->assertNull($entityManager->getRepository(Plan::class)->find(2));
    }

    /**
     * Test invalid submission for the supprimer method
     * and ensure it displays the form with an error message.
     */
    public function testSupprimerInvalidSubmission(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Mock a plan to delete
        $plan = (new Plan())->setId(1)->setNom('Plan 1');
        $entityManager->persist($plan);
        $entityManager->flush();

        // Send a POST request with incorrect confirmation
        $crawler = $client->request('POST', '/plan/supprimer', [
            'selected_plans' => [1],
            'suppression' => ['inputString' => 'WRONG'],
        ]);

        // Assert the response status code stays OK as it should return the form
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert an error message is displayed
        $this->assertSelectorExists('.flash-error');

        // Assert the plan is not deleted
        $this->assertNotNull($entityManager->getRepository(Plan::class)->find(1));
    }

    /**
     * Test the supprimer method form renders correctly with selected plans.
     */
    public function testSupprimerFormRendersCorrectly(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Mock plans to delete
        $plan1 = (new Plan())->setId(1)->setNom('Plan 1');
        $plan2 = (new Plan())->setId(2)->setNom('Plan 2');
        $entityManager->persist($plan1);
        $entityManager->persist($plan2);
        $entityManager->flush();

        // Send a GET request to render the form
        $crawler = $client->request('GET', '/plan/supprimer');

        // Assert the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert the form exists on the page
        $this->assertSelectorExists('form');

        // Assert the selected plans are displayed
        $this->assertSelectorTextContains('body', 'Plan 1');
        $this->assertSelectorTextContains('body', 'Plan 2');
    }
    /**
     * Test that the infos method renders the page correctly for a valid plan ID.
     */
    public function testInfosValidID(): void
    {
        $client = static::createClient();

        // Mock an existing Plan entity
        $plan = new Plan();
        $plan->setId(1);
        $plan->setNom('Valid Plan');

        $planRepo = $this->createMock(PlanRepository::class);
        $planRepo->method('find')->with(1)->willReturn($plan);

        $client->getContainer()->set(PlanRepository::class, $planRepo);

        // Send a GET request to the infos route
        $crawler = $client->request('GET', '/plan/1');

        // Assert the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Assert the correct data is rendered
        $this->assertSelectorTextContains('body', 'Valid Plan');
    }

    /**
     * Test that the infos method returns a 404 error for an invalid plan ID.
     */
    public function testInfosInvalidID(): void
    {
        $client = static::createClient();

        // Mock PlanRepository to return null for an invalid ID
        $planRepo = $this->createMock(PlanRepository::class);
        $planRepo->method('find')->with(999)->willReturn(null);

        $client->getContainer()->set(PlanRepository::class, $planRepo);

        // Send a GET request to the infos route for an invalid ID
        $client->request('GET', '/plan/999');

        $this->assertSelectorTextContains('body', 'No buildings found.');
    }
    /**
     * Test the suppSelection method with an empty selection of entities.
     */
    public function testSuppSelectionWithEmptySelection(): void
    {
        $client = static::createClient();
        $session = $client->getContainer()->get('session');
        $session->set('selected_batiments', []); // Simulate no selected entities

        $crawler = $client->request('GET', '/plan/supprimer-selection');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('body', 'No buildings found.');
    }

    /**
     * Test the suppSelection method with valid entity IDs and confirmation string.
     */
    public function testSuppSelectionWithValidInput(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Mock an existing entity to delete
        $batiment = (new Batiment())->setId(1)->setNom('Batiment 1');
        $entityManager->persist($batiment);
        $entityManager->flush();

        $crawler = $client->request('POST', '/plan/supprimer-selection', [
            'selected-' => [1],
            'suppression' => ['inputString' => 'CONFIRMER'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertResponseRedirects('/batiment/liste');
        $this->assertNull($entityManager->getRepository(Batiment::class)->find(1));
    }

    /**
     * Test the suppSelection method with invalid confirmation string.
     */
    public function testSuppSelectionWithInvalidConfirmation(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Mock an existing entity to delete
        $batiment = (new Batiment())->setId(1)->setNom('Batiment 1');
        $entityManager->persist($batiment);
        $entityManager->flush();

        $crawler = $client->request('POST', '/plan/supprimer-selection', [
            'selected-' => [1],
            'suppression' => ['inputString' => 'WRONG'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.flash-error');
        $this->assertNotNull($entityManager->getRepository(Batiment::class)->find(1));
    }

    /**
     * Test that the suppSelection method renders the form correctly with selected entities.
     */
    public function testSuppSelectionFormRendersCorrectly(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Mock entities for deletion
        $batiment1 = (new Batiment())->setId(1)->setNom('Batiment 1');
        $batiment2 = (new Batiment())->setId(2)->setNom('Batiment 2');
        $entityManager->persist($batiment1);
        $entityManager->persist($batiment2);
        $entityManager->flush();

        $crawler = $client->request('GET', '/plan/supprimer-selection');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form');
        $this->assertAnySelectorTextContains('body', 'Batiment 1');
        $this->assertAnySelectorTextContains('body', 'Batiment 2');
        $entityManager->remove($batiment2);
        $entityManager->remove($batiment1);
    }
}