<?php

namespace App\Tests\US1;

use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class salleTest extends WebTestCase
{
    public function test_page_salle_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle');

        $this->assertResponseIsSuccessful();
    }

    public function test_liste_salle_affiche(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle');

        $this->assertSelectorExists('table.salle');
    }

    public function test_D001_dans_liste(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle');

        $this->assertSelectorTextContains('table.salle td.nom', 'D001');
        $this->assertSelectorTextSame('table.salle td.bat', 'Batiment D');
        $this->assertSelectorTextSame('table.salle td.etage', '0');
        $this->assertSelectorTextSame('table.salle td.numSalle', '1');
    }

    public function testLienSupressionD001Dispo(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/salle');

        $selecteur = "table.salle td.supprimer a[href='/supprSalle?salle=" . $D001->getId() . "']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Supprimer");
    }

    public function testLienAjoutSalleDispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle');

        $selecteur = "a[href='/creerSalle']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Ajouter une salle");
    }
}
