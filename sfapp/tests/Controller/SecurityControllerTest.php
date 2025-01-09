<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    /**
     * Tests that the login page renders successfully.
     */
    public function testLoginPageRendersSuccessfully(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form'); // Assumes login form exists
    }

    /**
     * Tests that the login page displays the last entered username.
     */
    public function testLoginPageDisplaysLastUsername(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $session = self::$container->get('session');
        $session->set('_security.last_username', 'testuser');
        $session->save();

        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('input[name="username"]', 'testuser');
    }

    /**
     * Tests that the login page displays an error message if login failed.
     */
    public function testLoginPageDisplaysError(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $session = self::$container->get('session');
        $session->set('security.authentication_error', 'Invalid credentials.');
        $session->save();

        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.error', 'Invalid credentials.');
    }
}