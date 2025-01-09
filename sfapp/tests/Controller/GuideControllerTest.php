<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GuideControllerTest extends WebTestCase
{
    /**
     * Tests the `show` method in the `GuideController` class.
     * It ensures that the /guide route is accessible and renders the expected content.
     */
    public function testShowRouteRendersSuccessfully(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/guide');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html'); // Checks that the rendered page contains valid HTML content
    }

    /**
     * Tests that the /guide route renders the correct template by verifying the presence of specific content.
     */
    public function testShowRouteRendersCorrectTemplate(): void
    {
        $client = static::createClient();
        $client->request('GET', '/guide');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('title', 'Guide'); // Example test for specific template content
    }
}