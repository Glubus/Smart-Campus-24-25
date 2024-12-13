<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class US0Test extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
    }
    public function testPageAccueilExiste(): void
    {
        
        $this->assertResponseIsSuccessful();
    }

    public function test_smart_campus_redirige_vers_accueil(): void
    {
        
        $selecteur = "a[href='/']";

        $this->assertSelectorTextSame($selecteur, "Smart Campus");
    }

    public function test_navbar_accueil(): void
    {
        
        $selecteur = "ul li a[href='/']";

        $this->assertSelectorTextSame($selecteur, "Accueil");
    }

    public function test_navbar_guide(): void
    {

        $selecteur = "ul li a[href='/guide']";

        $this->assertSelectorTextSame($selecteur, "Guide");
    }

    public function testLienSalleDispo(): void
    {
        
        $selecteur = "ul li.dropdown a[href='/salle']";
        $this->assertSelectorExists($selecteur);

        $selecteur = "ul li.dropdown a[href='/salle/ajout']";
        $this->assertSelectorTextSame($selecteur, "Ajouter une salle");
    }

    public function testLienSADispo(): void
    {
        
        $selecteur = "ul li.dropdown a[href='/sa']";
        $this->assertSelectorExists($selecteur);

        $selecteur = "ul li.dropdown a[href='/sa/ajout']";
        $this->assertSelectorTextSame($selecteur, "Ajouter un SA");
    }

    public function testLienPlanDispo(): void
    {
        
        $selecteur = "ul li.dropdown a[href='/detail_plan']";
        $this->assertSelectorExists($selecteur);
    }

    public function testLienBatimentDispo(): void
    {
        
        $selecteur = "ul li.dropdown a[href='/batiment']";
        $this->assertSelectorExists($selecteur);

        $selecteur = "ul li.dropdown a[href='/batiment/ajout']";
        $this->assertSelectorTextSame($selecteur, "Ajouter un bâtiment");
    }
}
