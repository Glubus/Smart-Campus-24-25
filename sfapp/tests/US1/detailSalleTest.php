<?php

namespace App\Tests\US1;

use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class detailSalleTest extends WebTestCase
{
    public function test_page_detail_salle_existe_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');

        $crawler = $client->request('GET', '/salle/'.$D001->getId());

        $this->assertResponseIsSuccessful();
    }

    public function test_detail_salle_correct_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findByName('D001');
        $D = $D001->getBatiment();

        $crawler = $client->request('GET', '/salle/'.$D001->getId());
        $this->assertSelectorTextContains('', 'Nom : '.$D001->getSalleNom());
        $this->assertSelectorTextContains('', 'Bâtiment : '.$D->getNom());
        $this->assertSelectorTextContains('', 'Adresse : '.$D->getAdresse());
    }
}
