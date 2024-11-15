<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class US2Test extends WebTestCase
{
    public function test_page_SA_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/sa');

        $this->assertResponseIsSuccessful();
    }
}
