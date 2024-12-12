<?php

namespace App\Tests\US1_salle;

use App\Repository\BatimentRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class modifBatimentTest extends WebTestCase
{
    public function test_page_modif_batiment_existe_pour_batD(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'D']);

        $crawler = $client->request('GET', '/batiment/ajout?batiment='.$D->getId());
        $this->assertResponseIsSuccessful();
    }

    public function test_modif_batD_en_batE(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'D']);

        $crawler = $client->request('GET', '/batiment/ajout?batiment='.$D->getId());

        $form = $crawler->selectButton("Modifier")->form();
        $form['ajout_batiment[nom]'] = 'E';
        $client->submit($form);
        // Success redirection back to batiment page
        $this->assertResponseRedirects('/batiment');

        // Modification fait dans la base donnees
        $container = $client->getContainer();
        $E = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'E']);
        $this->assertNotNull($E);
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' =>'D']);
        $this->assertNull($D);

        // Modification batiment affiche correctement dans la liste
        $crawler = $client->request('GET', '/batiment');
        $this->assertSelectorTextSame('table tr:nth-of-type(2) td.nom', 'E');
    }
}
