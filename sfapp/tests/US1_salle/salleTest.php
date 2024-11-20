<?php

namespace App\Tests\US1_salle;

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

        $selecteur = "a[href='/salle/ajout']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Ajouter une salle");
    }

    public function testLienModifierSalleDispo_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/salle');

        $selecteur = "a[href='/modifierSalle?salle=" . $D001->getId() . "']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Modifier");
    }

    public function test_bar_recherche_salle_dispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle');

        $this->assertSelectorExists('form input#recherche_salle_salleNom');

        $selecteur = 'form button[type="submit"]';
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Rechercher");
    }

    public function test_resultat_recherche_salle(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle');

        $form = $crawler->selectButton("Rechercher")->form();

        $form['recherche_salle[salleNom]'] = 'D0';
        $client->submit($form);

        $this->assertSelectorTextContains('table.salle td.nom', 'D001');

        $form['recherche_salle[salleNom]'] = 'C';
        $client->submit($form);

        $this->assertSelectorTextContains('', "Aucune salle trouvée");
        $this->assertSelectorExists('a[href="/salle/ajout"]');
    }

    public function test_lien_info_salle_dispo(): void
    {
        $client = static::createClient();

        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/salle');

        $selecteur = "a[href='/salle/" . $D001->getId() . "']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Détails");
    }
}
