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

    public function testLienSalleDispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $selecteur = "a[href='/salle']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Salles");
    }

    public function testLienSADispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/accueil');

        $selecteur = "a[href='/sa']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Systèmes d'acquisition");
    }
}
