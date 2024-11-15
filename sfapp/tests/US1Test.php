<?php

namespace App\Tests;

use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class US1Test extends WebTestCase
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

    public function test_page_creerSalle_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');

        $this->assertResponseIsSuccessful();
    }

    public function test_valeur_champ_form_creerSalle(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');

        $selecteur = "form select#ajout_salle_batiment";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextContains($selecteur, "D");
        $this->assertSelectorTextContains($selecteur, "C");
        $this->assertSelectorTextNotContains($selecteur, "1");

        $selecteur = "form select#ajout_salle_etage";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextContains($selecteur, "Rez-de-chaussée");
        $this->assertSelectorTextContains($selecteur, "1");
        $this->assertSelectorTextNotContains($selecteur, "D");

        $selecteur = "form input#ajout_salle_numero";
        $this->assertSelectorExists($selecteur);
    }

    public function test_submit_form_valide_creerSalle(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');

        $form = $crawler->selectButton("Créer la salle")->form();
        $form["ajout_salle[batiment]"] = '1';
        $form['ajout_salle[etage]'] = '1';
        $form['ajout_salle[numero]'] = '1';
        $client->submit($form);
        $this->assertResponseRedirects('/salle');

        /*
        $crawler = $client->request('POST', '/your-endpoint', [
            'form_field_name' => 'value',
            // other form fields...
        ]);
         */

        $entityManager = self::$container->get('doctrine')->getManager();  // Get the entity manager
        $repository = $entityManager->getRepository(SalleRepository::class);
        $entity = $repository->findByName("C101");
        $this->assertNotNull($entity);
    }
}
