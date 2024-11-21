<?php

namespace App\Tests\US1_salle;

use App\Entity\Batiment;
use App\Repository\BatimentRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class supprBatimentTest extends WebTestCase
{
    public function test_page_supprBatiment_existe_pour_batC(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $C = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'C']);

        $crawler = $client->request('GET', '/batiment/'.$C->getId().'/suppression');
        $this->assertResponseIsSuccessful();
    }

    public function test_suppression_impossible_pour_batD(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'D']);

        $crawler = $client->request('GET', '/batiment/'.$D->getId().'/suppression');
        $this->assertResponseRedirects('/batiment');

        $crawler = $client->request('GET', '/batiment');
        $this->assertSelectorTextContains('.alert', 'Impossible de supprimer ce bâtiment');
    }

    public function test_bouton_annuler_dispo(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $C = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'C']);

        $crawler = $client->request('GET', '/batiment/'.$C->getId().'/suppression');
        $selecteur = "a.btn[href='/batiment']";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Annuler");
    }

    public function test_champ_confirmation_suppression_pour_batC(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $C = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'C']);

        $crawler = $client->request('GET', '/batiment/'.$C->getId().'/suppression');
        $this->assertSelectorTextSame('form label', "Entrez la phrase : C");
        $this->assertSelectorExists('form input');
    }

    public function test_suppression_valide_pour_batC(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $C = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'C']);

        $crawler = $client->request('GET', '/batiment/'.$C->getId().'/suppression');
        $form = $crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'C' ;
        $client->submit($form);
        $this->assertResponseRedirects('/batiment');

        $container = $client->getContainer();
        $C = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'C']);
        $this->assertNull($C);

        $crawler = $client->request('GET', '/batiment');
        $this->assertSelectorTextNotContains('table td.nom', 'C');

        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $batimentC = new Batiment();
        $batimentC->setNom('C');
        $batimentC->setAdresse('15 Rue François de Vaux de Foletier, 17000 La Rochelle');
        $entityManager->persist($batimentC);
        $entityManager->flush();
    }

    public function test_suppression_invalide_pour_batC(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $C = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'C']);

        $crawler = $client->request('GET', '/batiment/'.$C->getId().'/suppression');
        $form = $crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'Hello World' ;
        $client->submit($form);

        $this->assertSelectorTextContains('.alert', 'La saisie est incorrecte.');
    }
}
