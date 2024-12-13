<?php

namespace App\Tests\US1_salle;

use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class modifSalleTest extends WebTestCase
{
    public function test_page_modif_salle_existe_pour_D001(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);

        $crawler = $client->request('GET', '/modifierSalle?salle='.$D001->getId());

        $this->assertResponseIsSuccessful();
    }

    public function test_modif_D001_en_D101(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);

        $crawler = $client->request('GET', '/modifierSalle?salle=' . $D001->getId());

        $form = $crawler->selectButton("Modifier")->form();
        $form['ajout_salle[etage]'] = '1';
        $client->submit($form);
        // Success redirection back to salle page
        $this->assertResponseRedirects('/salle');

        // Modification fait dans la base donnees
        $container = $client->getContainer();
        $after = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001', 'etage' => 1]);
        $this->assertNotNull($after);
        $before = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001', 'etage' => 0]);
        $this->assertNull($before);

        // Modification salle affiche correctement dans la liste
        $crawler = $client->request('GET', '/salle');
        $this->assertSelectorTextSame('table td.etage', '1');
    }
}
