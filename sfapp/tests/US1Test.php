<?php

namespace App\Tests;

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

    public function test_liste_salle_affiche(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle');

        $this->assertSelectorExists('table.salle');
    }
}
