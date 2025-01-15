<?php

namespace App\Tests\Controller;

use App\Entity\Batiment;
use App\Entity\DetailIntervention;
use App\Entity\Etage;
use App\Entity\Salle;
use App\Entity\Utilisateur;
use App\Repository\DetailInterventionRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class TechnicienControllerTest extends WebTestCase
{
   public function testTechnicienAccessDeniedForGuests()
    {
        $client = static::createClient();

        // Attempt to access the route without being authenticated
        $client->request('GET', '/technicien');

        // Assert that the user is redirected to the login page (302 redirect)
        $this->assertResponseRedirects('/login');
    }

}
