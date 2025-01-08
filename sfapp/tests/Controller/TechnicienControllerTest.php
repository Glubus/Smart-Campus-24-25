<?php

namespace App\Tests\Controller;

use App\Entity\DetailIntervention;
use App\Entity\User;
use App\Repository\DetailInterventionRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Security;

class TechnicienControllerTest extends WebTestCase
{
    public function testViewTachesAccessDeniedWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/technicien/taches');

        $this->assertResponseStatusCodeSame(302); // Redirect to login
        $this->assertResponseRedirects('/login', 302);
    }

    public function testViewTachesDisplaysNonTermineTasks(): void
    {
        $client = static::createClient();

        $mockTechnicien = $this->createMock(User::class);
        $mockTechnicien->method('getEmail')->willReturn('technician@example.com');

        $mockRepository = $this->createMock(DetailInterventionRepository::class);
        $mockRepository->expects($this->once())
            ->method('findNonTermine')
            ->with($mockTechnicien)
            ->willReturn([new DetailIntervention(), new DetailIntervention()]);

        $client->getContainer()->set('security.token_storage', $this->mockSecurity($mockTechnicien));
        $client->getContainer()->set(DetailInterventionRepository::class, $mockRepository);

        $client->request('GET', '/technicien/taches');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr'); // Assuming tasks are displayed in a table
    }

    public function testViewTachesDisplaysAllTasksWhenFormSubmitted(): void
    {
        $client = static::createClient();

        $mockTechnicien = $this->createMock(User::class);
        $mockTechnicien->method('getEmail')->willReturn('technician@example.com');

        $mockRepository = $this->createMock(DetailInterventionRepository::class);
        $mockRepository->expects($this->once())
            ->method('findBy')
            ->with(['technicien' => $mockTechnicien])
            ->willReturn([new DetailIntervention()]);

        $mockFormFactory = $this->createMock(FormFactoryInterface::class);
        $mockForm = $this->createMock(Form::class);
        $mockForm->method('isSubmitted')->willReturn(true);
        $mockForm->method('isValid')->willReturn(true);

        $mockFormFactory->expects($this->once())
            ->method('createBuilder')
            ->willReturnSelf();

        $mockFormFactory->method('setMethod')->willReturnSelf();
        $mockFormFactory->method('add')->willReturnSelf();
        $mockFormFactory->method('getForm')->willReturn($mockForm);

        $client->getContainer()->set('security.token_storage', $this->mockSecurity($mockTechnicien));
        $client->getContainer()->set(DetailInterventionRepository::class, $mockRepository);
        $client->getContainer()->set(FormFactoryInterface::class, $mockFormFactory);

        $client->request('POST', '/technicien/taches');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table tbody tr'); // Assuming tasks are displayed in a table
    }

    private function mockSecurity($user)
    {
        $mockSecurity = $this->createMock(Security::class);
        $mockSecurity->method('getUser')->willReturn($user);
        return $mockSecurity;
    }
}