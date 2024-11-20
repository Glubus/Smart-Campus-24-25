<?php

namespace App\Tests\US2;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class saAjoutTest extends WebTestCase
{
    public function test_sa_ajout_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/sa/ajout');

        $this->assertResponseIsSuccessful();
    }
}
