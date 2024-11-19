<?php

namespace App\Tests\US1;

use App\Entity\Salle;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class creerSalleTest extends WebTestCase
{
    public function test_page_creerSalle_existe(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');

        $this->assertResponseIsSuccessful();
    }

    public function test_bouton_annuler_dispo(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');

        $selecteur = "a[href='/salle']";

        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextSame($selecteur, "Annuler");
    }

    public function test_valeur_champ_form_creerSalle(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');

        $selecteur = "form select#ajout_salle_batiment";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextContains($selecteur, "D");
        $this->assertSelectorTextContains($selecteur, "C");

        $selecteur = "form select#ajout_salle_etage";
        $this->assertSelectorExists($selecteur);
        $this->assertSelectorTextContains($selecteur, "Rez-de-chaussée");
        $this->assertSelectorTextContains($selecteur, "1");

        $selecteur = "form input#ajout_salle_numero";
        $this->assertSelectorExists($selecteur);
    }

    public function test_submit_form_valide_creerSalle(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');

        $form = $crawler->selectButton("Créer la salle")->form();
        $optionValue = null;
        foreach ($crawler->filter('select[name="ajout_salle[batiment]"] option') as $option) {
            if ($option->nodeValue === 'C') {
                $optionValue = $option->getAttribute('value');
                break;  // Stop once we've found the option with the text 'C'
            }
        }
        $form["ajout_salle[batiment]"] = $optionValue;
        $form['ajout_salle[etage]'] = '1';
        $form['ajout_salle[numero]'] = '1';
        $client->submit($form);
        $this->assertResponseRedirects('/salle');

        $container = $client->getContainer();
        $C101 = $container->get(SalleRepository::class)->findByName('C101');
        $this->assertNotNull($C101);

        $crawler = $client->request('GET', '/salle');
        $this->assertSelectorTextContains('table.salle tr:nth-of-type(2) td.nom', 'C101');

        $container = $client->getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $C101 = $container->get(SalleRepository::class)->findByName('C101');
        $entityManager->remove($C101);
        $entityManager->flush();
    }

    public function test_submit_form_invalide_numero_salle_non_entier(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');
        $optionValue = null;
        foreach ($crawler->filter('select[name="ajout_salle[batiment]"] option') as $option) {
            if ($option->nodeValue === 'C') {
                $optionValue = $option->getAttribute('value');
                break;  // Stop once we've found the option with the text 'C'
            }
        }

        $form = $crawler->selectButton("Créer la salle")->form();
        $form["ajout_salle[batiment]"] = $optionValue;
        $form['ajout_salle[etage]'] = '1';
        $form['ajout_salle[numero]'] = 'AB';
        $client->submit($form);

        $this->assertSelectorTextContains('.alert', 'Entiers uniquement');
    }

    public function test_submit_form_invalide_salle_duplique(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/creerSalle');
        $optionValue = null;
        foreach ($crawler->filter('select[name="ajout_salle[batiment]"] option') as $option) {
            if ($option->nodeValue === 'D') {
                $optionValue = $option->getAttribute('value');
                break;  // Stop once we've found the option with the text 'C'
            }
        }

        $form = $crawler->selectButton("Créer la salle")->form();
        $form["ajout_salle[batiment]"] = $optionValue;
        $form['ajout_salle[etage]'] = '0';
        $form['ajout_salle[numero]'] = '1';
        $client->submit($form);

        $this->assertSelectorTextContains('.alert', 'Cette salle existe déjà');
    }
}
