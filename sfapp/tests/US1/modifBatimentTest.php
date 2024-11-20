<?php

namespace App\Tests\US1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class modifBatimentTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
