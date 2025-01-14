<?php

namespace App\Tests\Controller;

use App\Entity\SA;
use App\Entity\Utilisateur;
use App\Repository\SARepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class SAControllerTest extends WebTestCase
{
    /**
     * Test case for listing all `SA` entities.
     */
    public function testLister_AllSAWithoutRights(): void
    {
        $client = static::createClient();

        $client->request('GET', '/sa');
        $this->assertResponseStatusCodeSame(302); // redirection to login
    }
    public function testLister_AllSAwithRights(): void
    {               
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
        $client->loginUser($user);
        $client->request('GET', '/sa');
        $this->assertResponseStatusCodeSame(200);

    }

    public function testAjoutSA_ShowForm(): void
    {
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/sa/ajouter');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="ajout_sa"]');
    }
    public function testAjoutSA_FormSubmitted_InvalidName(): void
    {
        // Création du client
        $client = static::createClient();

        // Connexion d'un utilisateur existant
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
        $client->loginUser($user);

        // Ajouter un SA dans la base pour simuler un nom déjà pris
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $existingSA = new SA();
        $existingSA->setNom('Duplicate SA'); // Nom déjà existant en base
        $entityManager->persist($existingSA);
        $entityManager->flush();

        // Simuler la soumission du formulaire avec un nom en doublon
        $crawler = $client->request('POST', '/sa/ajouter', [
            'ajout_sa' => [
                'nom' => 'Duplicate SA', // Même nom que l'existant
            ],
        ]);

        // Vérifier que le message d'erreur est présent
        $this->assertSelectorTextContains('.alert.alert-danger.mt-1', 'Le nom saisi est déjà utilisé.');
        // Nettoyage de la base (supprimer le SA ajouté pour le test)
        $entityManager->remove($existingSA);
        $entityManager->flush();
    }

    public function testAjoutSA_FormSubmitted_ValidName(): void
    {
        // Créer un client de test
        $client = static::createClient();

        // Récupérer un utilisateur existant et se connecter
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']); // ou l'identifiant correct
        $client->loginUser($user);

        // Charger la page contenant le formulaire
        $crawler = $client->request('GET', '/sa/ajouter');

        // Soumettre le formulaire en remplissant les champs correctement
        $client->submitForm('Créer', [
            'ajout_sa[nom]' => 'ESP-test', // L'association nom => valeur conformément au formulaire
        ]);

        // Vérifier la redirection après soumission
        $this->assertResponseRedirects('/sa');
        $client->followRedirect();

        // Vérifier en base de données que l'entité a été créée
        $SARepository = static::getContainer()->get(SARepository::class);
        $newSA = $SARepository->findOneBy(['nom' => 'ESP-test']);
        $this->assertNotNull($newSA, 'La nouvelle entité SA doit être');
        // Supprimer le SA après le test
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->remove($newSA);
        $entityManager->flush();
        $deletedSA = $SARepository->findOneBy(['nom' => 'ESP-test']);
        $this->assertNull($deletedSA, 'L\'entité SA doit être supprimée après le test');
    }
    public function testModifier_SAFound_FormNotSubmitted(): void
    {
        $client = static::createClient();
        // Récupérer un utilisateur existant et se connecter
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']); // ou l'identifiant correct
        $client->loginUser($user);

// Ajouter une entité SA dans la base pour le test
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $SA = new SA();
        $SA->setNom('Test SA'); // Définition des données pour le test
        $entityManager->persist($SA);
        $entityManager->flush();

        // Exécution de la requête pour modifier le SA créé
        $crawler = $client->request('GET', '/sa/modifier/' . $SA->getId());

        // Vérifications
        $this->assertResponseIsSuccessful(); // Vérifie que la réponse est 200
        $this->assertSelectorExists('form[name="ajout_sa"]'); // Vérifie que le formulaire apparaît
        $this->assertSelectorExists('input[name="ajout_sa[nom]"]'); // Vérifie que le champ "nom" existe
        $this->assertInputValueSame('ajout_sa[nom]', 'Test SA'); // Vérifie que le champ contient la valeur attendue
        $entityManager->remove($SA);
        $entityManager->flush();
    }

    public function testModifier_SANotFound(): void
    {
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']); // ou l'identifiant correct
        $client->loginUser($user);
        // Ajouter un SA dans la base, puis le supprimer explicitement
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $SA = new SA();
        $SA->setNom('SA Temporaire');
        $entityManager->persist($SA);
        $entityManager->flush();

        // Supprimer l'entité immédiatement pour simuler la condition de "not found"
        $entityManager->remove($SA);
        $entityManager->flush();

        // Tenter d'accéder au SA supprimé
        $client->request('GET', '/sa/modifier/' . $SA->getId());

        // Vérifications
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testModifier_FormSubmitted_InvalidName(): void
    {
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']); // ou l'identifiant correct
        $client->loginUser($user);
// Créer deux entités SA dans la base de données pour le test
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $existingSA = new SA();
        $existingSA->setNom('Existing SA'); // Nom qui existe déjà dans la base
        $entityManager->persist($existingSA);

        $SA = new SA();
        $SA->setNom('Test SA'); // SA que nous allons tenter de modifier
        $entityManager->persist($SA);

        $entityManager->flush();

        // Simuler la soumission du formulaire avec un nom déjà existant
        $crawler = $client->request('POST', '/sa/modifier/' . $SA->getId(), [
            'ajout_sa' => [
                'nom' => 'Existing SA', // Nom déjà utilisé
            ],
        ]);

        // Vérifications
        $entityManager->remove($SA);
        $entityManager->remove($existingSA);
        $entityManager->flush();
        $this->assertResponseIsSuccessful(); // Vérifie que la réponse est réussie (pas de redirection)
        $this->assertAnySelectorTextContains('alert-danger', 'Le nom saisi est déjà utilisé.'); // Vérifie le contenu du message
    }
    public function testModifier_FormSubmitted_ValidName(): void
    {
        // Création du client
        $client = static::createClient();

        // Connexion d'un utilisateur existant via le repository
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']); // ou l'identifiant correct
        $client->loginUser($user);

        // Ajouter un SA dans la base pour simuler une modification
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $SA = new SA();
        $SA->setNom('Ancien SA'); // Nom initial de l'entité
        $entityManager->persist($SA);
        $entityManager->flush();

        // Simuler la soumission du formulaire pour modifier le nom
        $client->request('POST', '/sa/modifier/' . $SA->getId(), [
            'ajout_sa' => [
                'nom' => 'Unique SA', // Nouveau nom
            ],
        ]);

        // Vérifier la redirection après la soumission réussie
        $this->assertResponseRedirects('/sa');

        // Suivre la redirection et vérifier le contenu de la page
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.saNom', 'Unique SA'); // Vérifie que le nouveau nom est affiché

        // Nettoyer la base après le test
        $entityManager->remove($SA);
        $entityManager->flush();
    }
}