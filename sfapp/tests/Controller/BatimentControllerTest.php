<?php

namespace App\Tests\Controller;

use App\Entity\Batiment;
use App\Repository\BatimentRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class tests the BatimentController and specifically the `liste` method,
 * which is responsible for rendering a list of buildings.
 */
class BatimentControllerTest extends WebTestCase
{

    /**
     * Test case for verifying the successful rendering of the list of buildings
     * when there are existing buildings in the database.
     */
    public function testListeWithExistingBatiments(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $batiment1 = new Batiment();
        $batiment2->setNom('Batiment D')
            ->setAdresse('123 Street A')
            ->setNbEtages(3);
        $entityManager->persist($batiment1);
        $batiment2 = new Batiment();
        $batiment2->setNom('Batiment C')
            ->setAdresse('123 Street A')
            ->setNbEtages(3);
        $entityManager->persist($batiment2);
        $entityManager->flush();

        $client->request('GET', '/batiment');
        $crawler = $client->getCrawler();
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Liste des batiment');
        $this->assertAnySelectorTextContains('.batimentNom', 'Batiment C');
        $this->assertAnySelectorTextContains('.batimentNom', 'Batiment D');
        $entityManager->remove($batiment1);
        $entityManager->remove($batiment2);
        $entityManager->flush();
    }
    public function testInfosWithExistingBatiment(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $batiment = new Batiment();
        $batiment->setNom('Batiment A')
            ->setAdresse('123 Street A')
            ->setNbEtages(3);
        $entityManager->persist($batiment);
        $entityManager->flush();


        // Act
        $client->request('GET', '/batiment/' . $batiment->getId());

        // Assert
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertAnySelectorTextContains('h2', 'Détails de la salle : Batiment A');
        $this->assertAnySelectorTextContains('.list-group-item', 'Batiment A');
        $this->assertAnySelectorTextContains('.list-group-item', '123 Street A');
        $this->assertAnySelectorTextContains('.list-group-item', '3');
        $entityManager->remove($batiment);
        $entityManager->flush();
    }



    /**
     * Test case to verify the rendering of the building list page when
     * there are no buildings in the database.
     */
    public function testListeWithNoBatiments(): void
    {
        $client = static::createClient();

        // Act
        $repository = $this->createMock(BatimentRepository::class);
        $repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([
                [],
            ]);
        $client->request('GET', '/batiment');
        $crawler = $client->getCrawler();

        // Assert
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h1', 'Liste des batiments');
        $this->assertCount(0, $crawler->filter('.batimentNom'));
        $this->assertSelectorTextContains('.no-items-message', 'Aucun bâtiment trouvé.');
    }

    /**
     * Test case to verify the successful response of the route when
     * accessing the building list page.
     */
    public function testListeResponseCode(): void
    {
        $client = static::createClient();

        // Act
        $client->request('GET', '/batiment');

        // Assert
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    /**
     * Test case for verifying the maximum number of floors (etages) of a building.
     */
    public function testGetMaxEtages(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $batiment = new Batiment();
        $batiment->setNom('Building A')
            ->setAdresse('123 Street A')
            ->setNbEtages(5);
        $entityManager->persist($batiment);
        $entityManager->flush();


        // Act
        $client->request('GET', '/batiment/' . $batiment->getId() . '/max-etages');

        // Assert
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['maxEtages' => 5]),
            $client->getResponse()->getContent()
        );
        $entityManager->remove($batiment);
        $entityManager->flush();
    }


    /**
     * Test case to verify the successful deletion of selected buildings after confirmation.
     */
    public function testSuppSelectionWithSelectedBatiments(): void
    {
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $batiment1 = new Batiment();
        $batiment1->setNom('Batiment D')
            ->setAdresse('123 Street A')
            ->setNbEtages(3);
        $entityManager->persist($batiment1);

        $batiment2 = new Batiment();
        $batiment2->setNom('Batiment C')
            ->setAdresse('456 Street B')
            ->setNbEtages(5);
        $entityManager->persist($batiment2);

        $entityManager->flush();

        $client->disableReboot();

        $client->request('POST', '/batiment/supprimer-selection', [
            'selected' => [$batiment1->getId(), $batiment2->getId()]
        ]);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'suppression[inputString]' => 'CONFIRMER'
        ]);

        // Act
        $client->submit($form);

        $this->assertNull($entityManager->getRepository(Batiment::class)->find($batiment1->getId()));
        $this->assertNull($entityManager->getRepository(Batiment::class)->find($batiment2->getId()));
        $entityManager->remove($batiment1);
        $entityManager->remove($batiment2);
    }


    /**
     * Test case to verify the handling of invalid confirmation input during deletion.
     */
    public function testSuppSelectionInvalidConfirmation(): void
    {
        // Arrange
        $client = static::createClient();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $batiment = new Batiment();
        $batiment->setNom('Batiment D')
            ->setAdresse('123 Street A')
            ->setNbEtages(3);
        $entityManager->persist($batiment);
        $entityManager->flush();
        $client->disableReboot();

        $client->request('POST', '/batiment/supprimer-selection', [
            'selected' => [$batiment->getId()]
        ]);

        $form = $client->getCrawler()->selectButton('Submit')->form([
            'suppression[inputString]' => 'INVALID_CONFIRMATION'
        ]);

        // Act
        $client->submit($form);

        // Assert
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertNotNull($entityManager->getRepository(Batiment::class)->find($batiment->getId()));
        $entityManager->remove($batiment);
        $entityManager->flush();
    }
}