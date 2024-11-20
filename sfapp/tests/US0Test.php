<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class US0Test extends WebTestCase
{
    public function testPageAccueilExiste(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $this->assertResponseIsSuccessful();
    }

    public function test_smart_campus_redirige_vers_accueil(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $selecteur = "a[href='/accueil']";

        $this->assertSelectorTextSame($selecteur, "Smart Campus");
    }

    public function test_navbar_accueil(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $selecteur = "ul li a[href='/accueil']";

        $this->assertSelectorTextSame($selecteur, "Accueil");
    }

    public function testLienSalleDispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $selecteur = "ul li.dropdown a[href='/salle']";
        $this->assertSelectorExists($selecteur);

        $selecteur = "ul li.dropdown a[href='/salle/ajout']";
        $this->assertSelectorTextSame($selecteur, "Ajouter une salle");
    }

    public function testLienSADispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $selecteur = "ul li.dropdown a[href='/sa']";
        $this->assertSelectorExists($selecteur);

        $selecteur = "ul li.dropdown a[href='/sa/ajout']";
        $this->assertSelectorTextSame($selecteur, "Ajouter un SA");
    }

    public function testLienPlanDispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $selecteur = "ul li.dropdown a[href='/plan']";
        $this->assertSelectorExists($selecteur);

        $selecteur = "ul li.dropdown a[href='/plan/ajout']";
        $this->assertSelectorTextSame($selecteur, "Ajouter un plan");
    }

    public function testLienBatimentDispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $selecteur = "ul li.dropdown a[href='/batiment']";
        $this->assertSelectorExists($selecteur);

        $selecteur = "ul li.dropdown a[href='/batiment/ajout']";
        $this->assertSelectorTextSame($selecteur, "Ajouter un batiment");
    }
}
