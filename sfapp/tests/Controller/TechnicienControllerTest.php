<?php

namespace App\Tests\Controller;

use App\Entity\Batiment;
use App\Entity\DetailIntervention;
use App\Entity\Etage;
use App\Entity\Salle;
use App\Entity\Utilisateur;
use App\Repository\DetailInterventionRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class TechnicienControllerTest extends WebTestCase
{
    public function testViewTachesAccessDeniedWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/technicien/taches');

        $this->assertResponseStatusCodeSame(302); // Redirect to login
        $this->assertResponseRedirects('/login', 302);
    }

    public function testViewTachesDisplaysNonTermineTasks(): void
    {
        $client = static::createClient();

        $mockTechnicien = $this->createTestUser(
            'John',
            'Doe',
            'technician@example.com',
            '123 Main St',
            'ROLE_TECHNICIEN',
            'password'
        );

        $mockRepository = $this->createMock(DetailInterventionRepository::class);
        $mockRepository->expects($this->once())
            ->method('findNonTermine')
            ->with($mockTechnicien)
            ->willReturn([new DetailIntervention(), new DetailIntervention()]);

        $client->getContainer()->set('security.helper', $this->mockSecurity($mockTechnicien)); // corrected key
        $client->getContainer()->set(DetailInterventionRepository::class, $mockRepository);

        $client->request('GET', '/technicien/taches');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr'); // Assuming tasks are displayed in a table
    }

    public function testViewTachesDisplaysAllTasksWhenFormSubmitted(): void
    {
        $client = static::createClient();

        $mockTechnicien = $this->createTestUser(
            'Jane',
            'Smith',
            'technician2@example.com',
            '456 Side St',
            'ROLE_TECHNICIEN',
            'password123'
        );

        $mockRepository = $this->createMock(DetailInterventionRepository::class);
        $mockRepository->expects($this->once())
            ->method('findBy')
            ->with(['technicien' => $mockTechnicien])
            ->willReturn([new DetailIntervention()]);

        $mockFormFactory = $this->createMock(FormFactoryInterface::class);
        $mockForm = $this->createMock(Form::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(true);

        $mockFormFactory->expects($this->once())
            ->method('createBuilder')
            ->willReturnSelf();

        $mockFormFactory->method('setMethod')->willReturnSelf();
        $mockFormFactory->method('add')->willReturnSelf();
        $mockFormFactory->method('getForm')->willReturn($mockForm);

        $client->getContainer()->set('security.helper', $this->mockSecurity($mockTechnicien)); // corrected key
        $client->getContainer()->set(DetailInterventionRepository::class, $mockRepository);
        $client->getContainer()->set(FormFactoryInterface::class, $mockFormFactory);

        $client->request('POST', '/technicien/taches');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr'); // Assuming tasks are displayed in a table
    }

    private function mockSecurity($user)
    {
        $mockSecurity = $this->createMock(Security::class);
        $mockSecurity->method('getUser')->willReturn($user);
        return $mockSecurity;
    }

    public function testTechnicianCanAddACommentWithValidInput(): void
    {
        $client = static::createClient();

        // Création d'un utilisateur technicien simulé
        $mockTechnicien = $this->createTestUser(
            'Jake',
            'Brown',
            'technician3@example.com',
            '789 Another St',
            'ROLE_TECHNICIEN',
            'securepassword'
        );

        // Création de la salle avec dépendances via makeSalle
        $mockSalle = $this->makeSalle('D205', 'Batiment D', 2, 5, 5);

        // Ajout d'une correction pour éviter la création de propriétés dynamiques

            $mockSalle->plans = null;


        // Mock de SalleRepository pour retourner la salle simulée
        $mockSalleRepository = $this->createMock(SalleRepository::class);
        $mockSalleRepository->expects($this->once())
            ->method('findByName') // Assurez-vous que cette méthode est correcte
            ->willReturn($mockSalle);

        // Mock de l'objet Security pour simuler l'utilisateur connecté
        $mockSecurity = $this->createMock(Security::class);
        $mockSecurity->method('getUser')->willReturn($mockTechnicien);

        // Injection des mocks dans le conteneur
        $client->getContainer()->set('security.helper', $mockSecurity);
        $client->getContainer()->set(SalleRepository::class, $mockSalleRepository);

        // Requête POST vers l'URL avec un ID dynamique
        $crawler = $client->request(
            'POST',
            '/technicien/commentaire/' . $mockTechnicien->getId(),
            [], // Pas de fichiers envoyés
            [], // Pas de données multipart
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], // Form-data format
            http_build_query([ // Encodage des données pour le format formulaire
                'description' => 'Test Commentaire',
                'salle' => $mockSalle->getId()
            ])
        );

        // Vérification que la réponse est une redirection
        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects(
            $client->getContainer()->get('router')->generate('app_technicien_commentaire'),
            302
        );

        // Ajout d'une assertion pour vérifier que le détail d'intervention est créé
        $detailIntervention = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(DetailIntervention::class)
            ->findOneBy(['description' => 'Test Commentaire']);

        $this->assertNotNull($detailIntervention);
        $this->assertEquals($mockTechnicien->getId(), $detailIntervention->getTechnicien()->getId());
        $this->assertEquals($mockSalle->getId(), $detailIntervention->getSalle()->getId());
    }


    public function testTechnicianCanAddACommentWithInvalidInput(): void
    {
        $client = static::createClient();

        $mockTechnician = $this->createTestUser(
            'Jake',
            'Brown',
            'technician3@example.com',
            '789 Another St',
            'ROLE_TECHNICIEN',
            'securepassword'
        );

        $mockSalleRepository = $this->createMock(SalleRepository::class);
        $mockSalleRepository->expects($this->once())
            ->method('findByName')
            ->willReturn(new Salle());

        $mockSecurity = $this->createMock(Security::class);
        $mockSecurity->method('getUser')->willReturn($mockTechnician);

        $client->getContainer()->set('security.token_storage', $mockSecurity);
        $client->getContainer()->set(SalleRepository::class, $mockSalleRepository);

        $crawler = $client->request(
            'POST',
            '/technicien/commentaire/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'description' => '', // Invalid input example
                'salle' => 'invalid_salle' // Invalid salle id example
            ])
        );

        $this->assertResponseStatusCodeSame(400); // Bad Request
        $this->assertSelectorExists('.error-message'); // Assuming error messages are displayed
    }

    public function createTestUser(string $nom, string $prenom, string $email, string $adresse, string $role, string $password): Utilisateur
    {
        $technicien = new Utilisateur();
        $technicien->setNom($nom);
        $technicien->setPrenom($prenom);
        $technicien->setEmail($email);
        $technicien->setAdresse($adresse);
        $technicien->setRoles([$role]);
        $technicien->setPassword($password);
        $technicien->generateUsername();
        return $technicien;
    }
    private function makeSalle(
        string $nomSalle,
        string $nomBatiment,
        int $etageId,
        int $fenetres = 0,
        int $radiateurs = 0
    ): Salle {
        // Création du bâtiment
        $batiment = new Batiment();
        $batiment->setNom($nomBatiment);

        // Création de l'étage
        $etage = new Etage();
        $etage->setNom("Etage $etageId");
        $etage->setBatiment($batiment);

        // Ajout de l'étage au bâtiment
        $batiment->addEtage($etage);

        // Création de la salle
        $salle = new Salle();
        $salle->setId(uniqid()); // ID unique pour les tests
        $salle->setNom($nomSalle);
        $salle->setFenetre($fenetres);
        $salle->setRadiateur($radiateurs);
        $salle->setEtage($etage);

        return $salle;
    }
}
