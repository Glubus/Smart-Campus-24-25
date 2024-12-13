<?php

namespace App\Tests\US1_salle;

use App\Entity\Salle;
use App\Repository\BatimentRepository;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ajoutSalleTest extends WebTestCase
{
    public function test_page_salle_ajout_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle/ajout');

        $this->assertResponseIsSuccessful();
    }

    public function test_bouton_annuler_dispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle/ajout');

        $selecteur = "a.btn[href='/salle']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Annuler");
    }

    public function test_valeur_champ_form_salle_ajout(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle/ajout');

        $selecteur = "form select#ajout_salle_batiment";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextContains($selecteur, "D");
        $this->assertSelectorTextContains($selecteur, "C");

        $selecteur = "form input#ajout_salle_etage";
        $this->assertSelectorExists($selecteur);

        $selecteur = "form input#ajout_salle_nom";
        $this->assertSelectorExists($selecteur);
    }

    public function test_submit_form_valide_nouvelle_salle(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle/ajout');

        $container = $client->getContainer();
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' => 'D'])->getId();

        $form = $crawler->selectButton("Créer la salle")->form();
        $form["ajout_salle[batiment]"] = $D;
        $form['ajout_salle[etage]'] = '1';
        $form['ajout_salle[nom]'] = 'D101';
        $client->submit($form);
        $this->assertResponseRedirects('/salle');

        $D101 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D101', 'batiment' => $D]);
        $this->assertNotNull($D101);

        $crawler = $client->request('GET', '/salle');
        $sallesAffiches=$crawler->filter('table td.nom')->each(
            function (Crawler $node):string {
                return $node->text();
            });
        $this->assertContains('D101', $sallesAffiches);
    }


    public function test_submit_form_invalide_salle_duplique_dans_meme_batiment(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle/ajout');

        $container = $client->getContainer();
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' => 'D'])->getId();

        $form = $crawler->selectButton("Créer la salle")->form();
        $form["ajout_salle[batiment]"] = $D;
        $form['ajout_salle[etage]'] = '0';
        $form['ajout_salle[nom]'] = 'D001';
        $client->submit($form);

        $this->assertSelectorTextContains('.alert', 'Cette salle existe déjà');
    }

    public function test_submit_form_invalide_nbEtage_depasse(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle/ajout');

        $container = $client->getContainer();
        $D = $container->get(BatimentRepository::class)->findOneBy(['nom' => 'D']);

        $form = $crawler->selectButton("Créer la salle")->form();
        $form["ajout_salle[batiment]"] = $D->getId();
        $form['ajout_salle[etage]'] = '999';
        $form['ajout_salle[nom]'] = 'Blabla';
        $client->submit($form);

        $this->assertSelectorExists('.alert');
        $this->assertSelectorTextContains('.alert', $D->getNbEtages());
    }

    /*public function test_submit_form_valide_salle_duplique_dans_batiments_differents(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/salle/ajout');

        $container = $client->getContainer();
        $C = $container->get(BatimentRepository::class)->findOneBy(['nom' => 'C'])->getId();

        $form = $crawler->selectButton("Créer la salle")->form();
        $form["ajout_salle[batiment]"] = $C;
        $form['ajout_salle[etage]'] = '1';
        $form['ajout_salle[nom]'] = 'D001';
        $client->submit($form);
        $this->assertResponseRedirects('/salle');

        $D001 = $container->get(SalleRepository::class)->findOneBy(['nom' => 'D001', 'batiment' => $C]);
        $this->assertNotNull($D001);

        $crawler = $client->request('GET', '/salle');
        $sallesAffiches=$crawler->filter('table td.nom')->each(
            function (Crawler $node):string {
                return $node->text();
            });
        $filtered = array_filter($sallesAffiches, function($value) {
            return $value == 'D001';
        });
        $this->assertCount(2, $filtered);
    }*/
}
