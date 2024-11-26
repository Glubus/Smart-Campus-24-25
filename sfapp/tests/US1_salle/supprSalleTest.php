<?php

namespace App\Tests\US1_salle;

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
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());

        $this->assertResponseIsSuccessful();
    }

    public function test_bouton_annuler_dispo(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());
        $selecteur = "a.btn[href='/salle']";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Annuler");
    }

    public function test_champ_confirmation_suppression_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());
        $this->assertSelectorTextSame('form label', "Entrez la phrase : D001");
        $this->assertSelectorExists('form input');
    }

    public function test_suppression_valide_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());
        $form = $crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'D001' ;
        $client->submit($form);
        $this->assertResponseRedirects('/salle');

        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);
        $this->assertNull($D001);

        $crawler = $client->request('GET', '/salle');
        $this->assertSelectorTextNotContains('table.salle td.nom', 'D001');
    }

    public function test_suppression_invalide_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);

        $crawler = $client->request('GET', '/supprSalle?salle='.$D001->getId());
        $form = $crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'Hello World' ;
        $client->submit($form);

        $this->assertSelectorTextContains('.alert', 'La saisie est incorrect.');
    }
}
