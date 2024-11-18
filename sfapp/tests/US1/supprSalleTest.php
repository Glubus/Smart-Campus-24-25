<?php

namespace App\Tests\US1;

use App\Entity\EtageSalle;
use App\Entity\Salle;
use App\Repository\BatimentRepository;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class supprSalleTest extends WebTestCase
{
    public function test_page_supprSalle_existe_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());

        $this->assertResponseIsSuccessful();
    }

    public function test_bouton_annuler_dispo(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());
        $selecteur = "a[href='/salle']";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Annuler");
    }

    public function test_champ_confirmation_suppression_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());
        $this->assertSelectorTextSame('form label', "Entrez la phrase : D001");
        $this->assertSelectorExists('form input');
    }

    public function test_suppression_valide_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());
        $form = $crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'D001' ;
        $client->submit($form);
        $this->assertResponseRedirects('/salle');

        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');
        $this->assertNull($D001);

        $crawler = $client->request('GET', '/salle');
        $this->assertSelectorNotExists('table.salle td.nom');

        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' => 'D']);
        $D001 = new Salle();
        $D001->setNumero("1");
        $D001->setEtage(EtageSalle::REZDECHAUSSEE);
        $D001->setBatiment($D);
        $entityManager->persist($D001);
        $entityManager->flush();
    }

    public function test_suppression_invalide_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());
        $form = $crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'Hello World' ;
        $client->submit($form);

        $this->assertSelectorTextContains('.alert', 'Mauvaise phrase saisie');
    }
}
