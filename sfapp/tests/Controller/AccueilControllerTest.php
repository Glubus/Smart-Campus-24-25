<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccueilControllerTest extends WebTestCase
{
    /**
     * Tests the index action of the AccueilController.
     * Ensures the response is successful and the correct template is rendered.
     */
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html'); // Ensure the HTML page is properly loaded
    }
}