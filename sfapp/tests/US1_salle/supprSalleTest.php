<?php

namespace App\Tests\US1_salle;

use App\Entity\EtageSalle;
use App\Entity\Salle;
use App\Repository\BatimentRepository;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class supprSalleTest extends WebTestCase
{
    private $crawler;
    private $client;
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $container = $this->client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);

        $this->crawler = $this->client->request('POST', '/salle/supprimer-selection',[
            'selected_salles' => [$D001->getId()]
        ]);
    }
    public function test_page_supprSalle_existe_pour_D001(): void
    {
        $this->crawler = $this->client->request('GET', '/salle/supprimer-selection');

        $this->assertResponseIsSuccessful();
    }

    public function test_bouton_annuler_dispo(): void
    {
        $selecteur = "a.btn[href='/salle']";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Annuler");
    }

    public function test_champ_confirmation_suppression_pour_D001(): void
    {
        $this->assertSelectorTextSame('form label', "Entrez la phrase : CONFIRMER");
        $this->assertSelectorExists('form input');
    }

    public function test_suppression_valide_pour_D001(): void
    {
        $form = $this->crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'CONFIRMER' ;
        $this->client->submit($form);
        $this->assertResponseRedirects('/salle');

        $container = $this->client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);
        $this->assertNull($D001);

        $this->crawler = $this->client->request('GET', '/salle');
        $this->assertSelectorTextNotContains('table td.nom', 'D001');
    }

    public function test_suppression_valide_pour_D001_et_C001(): void
    {
        $container = $this->client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);
        $D003 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D003']);

        $this->crawler = $this->client->request('POST', '/salle/supprimer-selection',[
            'selected_salles' => [$D001->getId(), $D003->getId()]
        ]);

        $form = $this->crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'CONFIRMER' ;
        $this->client->submit($form);
        $this->assertResponseRedirects('/salle');

        $container = $this->client->getContainer();
        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001']);
        $this->assertNull($D001);
        $D003 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D003']);
        $this->assertNull($D003);

        $this->crawler = $this->client->request('GET', '/salle');
        $sallesAffiches=$this->crawler->filter('table td.nom')->each(
            function (Crawler $node):string {
                return $node->text();
            });
        $this->assertNotContains('D001', $sallesAffiches);
        $this->assertNotContains('D003', $sallesAffiches);
    }


    public function test_suppression_invalide_pour_D001(): void
    {
        $form = $this->crawler->selectButton("Supprimer")->form();
        $form["suppression[inputString]"] = 'Hello World' ;
        $this->client->submit($form);

        $this->assertSelectorTextContains('.alert', 'La saisie est incorrect.');
    }
}
