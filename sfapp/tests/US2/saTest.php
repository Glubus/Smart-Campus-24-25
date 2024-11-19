<?php

namespace App\Tests\US2;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class saTest extends WebTestCase
{
    public function test_page_SA_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/sa');

        $this->assertResponseIsSuccessful();
    }
}
