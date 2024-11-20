<?php

namespace App\Tests\US1_salle;

use App\Repository\BatimentRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ajoutBatimentTest extends WebTestCase
{
    public function test_page_batiment_ajout_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/batiment/ajout');

        $this->assertResponseIsSuccessful();
    }

    public function test_bouton_annuler_dispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/batiment/ajout');

        $selecteur = "a.btn[href='/batiment']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Annuler");
    }

    public function test_valeur_champ_form_batiment_ajout(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'batiment/ajout');

        $this->assertSelectorExists("form input#ajout_batiment_nom");
        $this->assertSelectorExists("form input#ajout_batiment_adresse");
    }

    public function test_submit_form_valide_batiment_ajout(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/batiment/ajout');

        $form = $crawler->selectButton("Ajouter")->form();
        $form["ajout_batiment[nom]"] = 'A';
        $form["ajout_batiment[adresse]"] = '123 rue janisse';
        $client->submit($form);
        $this->assertResponseRedirects('/batiment');

        $container = $client->getContainer();
        $A = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'A']);
        $this->assertNotNull($A);

        $crawler = $client->request('GET', '/batiment');
        $this->assertSelectorTextContains('table tr:nth-of-type(3) td.nom', 'A');
    }

    public function test_submit_form_invalide_nom_batiment_duplique(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/batiment/ajout');

        $form = $crawler->selectButton("Ajouter")->form();
        $form["ajout_batiment[nom]"] = 'D';
        $form["ajout_batiment[adresse]"] = '123 rue janisse';
        $client->submit($form);

        $this->assertSelectorTextContains('.alert', 'Ce batiment existe déjà');
    }
}
