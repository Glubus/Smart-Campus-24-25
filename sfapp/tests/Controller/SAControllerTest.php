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
    public function testLister_SansAvoirLesDroits(): void
    {
        $client = static::createClient();

        $client->request('GET', '/sa');
        // Vérifier que le code de statut est un 302 (redirection)
        $this->assertResponseStatusCodeSame(302, 'L\'utilisateur sans droits devrait être redirigé.');

        // Vérifier que la redirection est vers la page de connexion
        $this->assertResponseRedirects('/login', null, 'La redirection devrait pointer vers /login.');

        // Suivre la redirection pour analyser la page cible
        $crawler = $client->followRedirect();

        // Vérifier que la page redirigée contient bien le formulaire de connexion
        $this->assertSelectorExists('form[action="/login"]', 'Le formulaire de connexion avec la bonne action est attendu.');

        // Vérifier que le formulaire utilise la méthode POST
        $formNode = $crawler->filter('form[action="/login"]')->first();
        $this->assertSame('POST', $formNode->attr('method'), 'Le formulaire de connexion doit utiliser la méthode POST.');
    }
    public function testLister_AvecLesDroits(): void
    {               
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
        $client->loginUser($user);
        $client->request('GET', '/sa');
        $this->assertResponseStatusCodeSame(200);
        // Vérifier que la page contient un container pour afficher la listes des SA
        $this->assertSelectorExists('.listeBatiments', 'La page doit afficher un container d une liste des entités SA.');


        // Vérifier le titre ou un en-tête spécifique de la page (si défini)
        $this->assertSelectorTextContains('h1', 'Listes des SA', 'Le titre de la page doit être "Liste des SA".');

        // Vérifier la présence d'un bouton d'ajout d'une nouvelle entité SA (si attendu)
        $this->assertSelectorExists('.btnAjout', 'Un bouton pour ajouter un nouvel SA doit être présent.');

    }
    public function testAjoutSA_AfficherForm(): void
    {
        $client = static::createClient();
        $utilisateurRepository = static::getContainer()->get(UtilisateurRepository::class);
        $user = $utilisateurRepository->findOneBy(['username' => 'maxaz']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/sa/ajouter');

        // Vérifie que la réponse est bien un succès
        $this->assertResponseIsSuccessful();

        // Vérifie que le formulaire avec le nom "ajout_sa" est présent dans la page
        $this->assertSelectorExists('form[name="ajout_sa"]');

        // Vérifie la présence d'un bouton de soumission dans le formulaire
        $this->assertSelectorExists('form[name="ajout_sa"] button[type="submit"]');

        // Vérifie qu'un champ spécifique nommé "nom" est présent dans le formulaire
        $this->assertSelectorExists('form[name="ajout_sa"] input[name="ajout_sa[nom]"]');

    }
    public function testAjoutSA_FormEnvoyer_NomValide(): void
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


        // Vérifier que l'utilisateur voit le formulaire
        $this->assertResponseStatusCodeSame(302); // La page doit faire un succès HTTP 200
        $this->assertResponseRedirects('/sa');
        $client->followRedirect();

        // Vérifier que l'utilisateur arrive sur une page de confirmation ou la liste concernée
        $this->assertResponseIsSuccessful(); // La page après redirection doit faire un succès HTTP 200
        // Vérifier en base de données que l'entité a été créée
        $SARepository = static::getContainer()->get(SARepository::class);
        $newSA = $SARepository->findOneBy(['nom' => 'ESP-test']);
        $this->assertNotNull($newSA, 'La nouvelle entité SA doit être ajoutée en base.');
        $this->assertEquals('ESP-test', $newSA->getNom(), 'Le nom de la nouvelle entité SA doit correspondre.');

        // Vérifier que l'entité est affichée dans la vue liste ou équivalent
        $this->assertAnySelectorTextContains('.batimentName', 'ESP-test', 'Le SA devrait apparaître dans la vue liste.');

        // Supprimer le SA après le test
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->remove($newSA);
        $entityManager->flush();

        // Vérifier que l'entité a bien été supprimée
        $deletedSA = $SARepository->findOneBy(['nom' => 'ESP-test']);
        $this->assertNull($deletedSA, 'L\'entité SA doit être supprimée après le test');
    }
    public function testModifier_SATrouver_FormNonEnvoyer(): void
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
        $this->assertSelectorTextContains('h1', 'Modifier sa', 'Le titre de la page de modification devrait être présent.');

        $entityManager->remove($SA);
        $entityManager->flush();
    }

    public function testModifier_SANonTrouver(): void
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

}