<?php

namespace App\Tests\US1_salle;

use App\Repository\BatimentRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class batimentTest extends WebTestCase
{
    public function test_page_batiment_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/batiment');

        $this->assertResponseIsSuccessful();
    }

    public function test_liste_batiment_affiche(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/batiment');

        $batAffiches=$crawler->filter('table td.nom')->each(
            function (Crawler $node):string {
                return $node->text();
            });

        $batAttendu=[
            'C',
            'D'
        ];

        $this->assertSame($batAffiches,$batAttendu);
    }

    public function testLienSupression_batD_Dispo(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'D']);

        $crawler = $client->request('GET', '/batiment');

        $selecteur = "table a[href='/batiment/" . $D->getId() . "/suppression']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Supprimer");
    }

    public function testLienAjoutBatimentDispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/batiment');

        $selecteur = "a[href='/batiment/ajout']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Ajouter un bâtiment");
    }
}
