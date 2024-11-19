<?php

namespace App\Tests\US2;

use App\Repository\SalleRepository;
use App\Repository\SARepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class saSupprTest extends WebTestCase
{
    public function test_page_sa_suppresion_existe_pour_sa01(): void
    {
        $client = static::createClient();
        $container = $client->getContainer();
        $SA01 = $container->get(SARepository::class)->findOneBy(['nom'=>'SA01']);

        $crawler = $client->request('GET', '/sa/'.$SA01->getId().'/suppression');
        $this->assertResponseIsSuccessful();
    }
}
