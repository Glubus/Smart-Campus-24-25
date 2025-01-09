<?php

namespace App\Tests\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AdminControllerTest extends WebTestCase
{
    /**
     * Test case to ensure the admin index page loads successfully
     */
    public function testIndexPageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test case to ensure the `/new` page loads successfully
     */
    public function testNewPageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/new');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test case for `getTechnicians` method without a name filter
     */
    public function testGetTechniciansWithoutName(): void
    {
        $repository = $this->createMock(UtilisateurRepository::class);
        $repository
            ->expects($this->once())
            ->method('findByRole')
            ->with('ROLE_TECHNICIEN')
            ->willReturn([
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Smith'],
            ]);

        $adminController = new \ReflectionClass(AdminController::class);
        $getTechniciansMethod = $adminController->getMethod('getTechnicians');
        $getTechniciansMethod->setAccessible(true);

        $result = $getTechniciansMethod->invokeArgs(
            (new AdminController()),
            [$repository, null]
        );

        $this->assertCount(2, $result);
        $this->assertEquals('John Doe', $result[0]['name']);
        $this->assertEquals('Jane Smith', $result[1]['name']);
    }

    /**
     * Test case for `getTechnicians` method with a name filter
     */
    public function testGetTechniciansWithName(): void
    {
        $repository = $this->createMock(UtilisateurRepository::class);
        $repository
            ->expects($this->once())
            ->method('findTechniciensByRoleAndNom')
            ->with('ROLE_TECHNICIEN', 'John')
            ->willReturn([
                ['id' => 1, 'name' => 'John Doe'],
            ]);

        $adminController = new \ReflectionClass(AdminController::class);
        $getTechniciansMethod = $adminController->getMethod('getTechnicians');
        $getTechniciansMethod->setAccessible(true);

        $result = $getTechniciansMethod->invokeArgs(
            (new AdminController()),
            [$repository, 'John']
        );

        $this->assertCount(1, $result);
        $this->assertEquals('John Doe', $result[0]['name']);
    }

    /**
     * Test case to verify form submission creates a new DetailIntervention entity
     */
    public function testFormSubmissionCreatesDetailIntervention(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $crawler = $client->request('GET', '/new');
        $form = $crawler->selectButton('Save')->form([
            'detail_intervention[description]' => 'Fix projector',
            'detail_intervention[salle]' => 1,
            'detail_intervention[technicien]' => 1,
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/admin');

        $intervention = $entityManager->getRepository(DetailIntervention::class)->findOneBy([
            'description' => 'Fix projector'
        ]);

        $this->assertNotNull($intervention);
        $this->assertEquals('Fix projector', $intervention->getDescription());
        $this->assertEquals(EtatIntervention::EN_ATTENTE, $intervention->getEtat());
    }

    /**
     * Test case to check if the index page displays technicians when no filter is applied
     */
    public function testIndexDisplaysTechnicians(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $utilisateurRepository = $this->createMock(UtilisateurRepository::class);
        $utilisateurRepository
            ->expects($this->once())
            ->method('findByRole')
            ->with('ROLE_TECHNICIEN')
            ->willReturn([
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Smith'],
            ]);

        $container->set(UtilisateurRepository::class, $utilisateurRepository);

        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'John Doe');
        $this->assertSelectorTextContains('body', 'Jane Smith');
    }

    /**
     * Test case to check if the index page applies filters and displays filtered technicians
     */
    public function testIndexDisplaysFilteredTechnicians(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $utilisateurRepository = $this->createMock(UtilisateurRepository::class);
        $utilisateurRepository
            ->expects($this->once())
            ->method('findTechniciensByRoleAndNom')
            ->with('ROLE_TECHNICIEN', 'John')
            ->willReturn([
                ['id' => 1, 'name' => 'John Doe'],
            ]);

        $container->set(UtilisateurRepository::class, $utilisateurRepository);

        $crawler = $client->request('GET', '/admin');
        $buttonCrawlerNode = $crawler->selectButton('Search'); // Assuming button label is 'Search'

        $form = $buttonCrawlerNode->form([
            'recherche_sa[nom]' => 'John',
        ]);

        $client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'John Doe');
        $this->assertSelectorTextNotContains('body', 'Jane Smith');
    }
}