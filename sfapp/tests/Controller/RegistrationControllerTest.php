<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form[name=registration_form]');
    }

    public function testRegistrationWithValidData(): void
    {
        $client = static::createClient();
        $client->followRedirects();

        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form();

        $formData = [
            'registration_form[prenom]' => 'John',
            'registration_form[nom]' => 'Doe',
            'registration_form[email]' => 'john.doe@example.com',
            'registration_form[adresse]' => '123 Main Street',
            'registration_form[plainPassword][first]' => 'Password123!',
            'registration_form[plainPassword][second]' => 'Password123!',
            'registration_form[roles]' => 'ROLE_USER',
        ];

        $form->setValues($formData);
        $client->submit($form);

        $this->assertResponseRedirects('/page/acceuil');
        $client->followRedirect();
        $this->assertSelectorExists('.welcome-message');
    }

    public function testRegistrationWithInvalidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form();

        $formData = [
            'registration_form[prenom]' => '',
            'registration_form[nom]' => '',
            'registration_form[email]' => 'invalid-email',
            'registration_form[plainPassword][first]' => 'short',
            'registration_form[plainPassword][second]' => 'different',
            'registration_form[roles]' => '',
        ];

        $form->setValues($formData);
        $crawler = $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.form-error-message');
    }

    public function testDuplicateUsernameError(): void
    {
        $client = static::createClient();
        $entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $user = new Utilisateur();
        $user->setUsername('existing_user');
        $user->setEmail('existing@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $this->getContainer()->get(UserPasswordHasherInterface::class)->hashPassword($user, 'Password123!')
        );
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form();

        $formData = [
            'registration_form[prenom]' => 'Jane',
            'registration_form[nom]' => 'Doe',
            'registration_form[email]' => 'existing@example.com',
            'registration_form[plainPassword][first]' => 'Password123!',
            'registration_form[plainPassword][second]' => 'Password123!',
            'registration_form[roles]' => 'ROLE_USER',
        ];

        $form->setValues($formData);
        $crawler = $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('.form-error-message');
    }
}