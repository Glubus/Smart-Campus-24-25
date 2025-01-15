<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\SA;
use App\Entity\Plan;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\DetailPlan;
use App\Form\AjoutBatimentType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function PHPUnit\Framework\isNull;

class   BatimentController extends AbstractController
{
    private const CONFIRMATION_PHRASE = 'CONFIRMER';

    /**
     * Handles the GET request to display the list of "batiment" entities.
     *
     * This method fetches all Batiment entities from the database and distributes them
     * across three columns for rendering purposes. The distribution is performed in a
     * cyclic manner:
     * - Entities with an even index are added to the first column.
     * - Entities with an odd index are added to the second column.
     * - Remaining entities are added to the third column.
     *
     * The resulting data is then passed to the 'batiment/liste.html.twig' template for rendering.
     *
     * @param EntityManagerInterface $entityManager The service responsible for handling database operations.
     * @return Response The rendered view of the batiment list.
     */
    #[Route('/batiment', name: 'app_batiment_liste')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        $batiments = $entityManager->getRepository(Batiment::class)->findAll();
        $index = 0;
        $col1 = [];
        $col2 = [];
        $col3 = [];
        foreach ($batiments as $batiment) {
            if($index%3 == 0){
                $col1[] = $batiment;
            } elseif ($index%3 == 1) {
                $col2[] = $batiment;
            } else {
                $col3[] = $batiment;
            }
            $index++;
        }

        return $this->render('batiment/liste.html.twig', [
            'col1' => $col1,
            'col2' => $col2,
            'col3' => $col3,
        ]);
        /*
        return $this->render('batiment/liste.html.twig', [
            'css' => 'batiment',
            'classItem' => "batiment",
            'items' => $batiments,
            'routeAjouter' => "app_batiment_ajouter",
            'classSpecifique' => ""
        ]);*/
    }

    /**
     * Handles the display of information for a specific building.
     *
     * The method retrieves a building entity by its ID using the provided repository
     * and renders the corresponding view with predefined settings and data.
     *
     * @param int $id The ID of the building to retrieve.
     * @param BatimentRepository $repository Repository used to fetch the building data.
     *
     * @return Response The rendered HTML response containing the building details.
     */
    #[Route('/batiment/{id}', name: 'app_batiment_infos', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function infos(int $id, BatimentRepository $repository): Response
    {
        $batiment = $repository->find($id);

        return $this->render('batiment/infos.html.twig', [
            'css' => 'batiment',
            'classItem' => "batiment",
            'item' => $batiment,
            'routeAjouter' => "app_batiment_ajouter",
            'classSpecifique' => ""
        ]);
    }


    /**
     * Handles the addition and editing of a building.
     *
     * This method creates or retrieves a building entity based on request data,
     * processes a form submission for the building, validates the input, and
     * persists the updated or new entity into the database. It ensures unique names
     * for floors, prevents duplication of building names, and displays appropriate
     * error messages when validation fails. Upon successful form submission and
     * processing, the user is redirected to the building list page.
     *
     * @param Request $request The HTTP request containing the form data and parameters.
     * @param BatimentRepository $repository Repository used to fetch or check building data.
     * @param EntityManagerInterface $entityManager Entity manager for persisting building data.
     *
     * @return Response The rendered HTML response for the form or the redirection to another route.
     */
    #[Route('/batiment/ajouter', name: 'app_batiment_ajouter')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function ajouter(Request $request, BatimentRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $batimentId = $request->get('batiment');
        $batiment = $batimentId ? $repository->find($batimentId) : new Batiment();

        $form = $this->createForm(AjoutBatimentType::class, $batiment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Access dynamically added "etages" data
            $etages = $request->request->all('form')['etages']; // Safely retrieve
            foreach ($etages as $key => $etageName) {
                if($etageName != null){
                    $batiment->renameEtage($key, $etageName);
                }
                else
                    $etages[$key] = $key;
            }

            if (!$this->isNomEtagesUnique($etages)) {
                $this->addFlash('error', 'Chaque étage doit avoir un nom unique.');
            } elseif ($repository->findOneBy(['nom' => $batiment->getNom()])) {
                $this->addFlash('error', 'Ce bâtiment existe déjà.');
            } else {
                $this->processEtages($batiment, $etages);
                $this->persistAndFlush($entityManager, $batiment);

                return $this->redirectToRoute('app_batiment_liste');
            }
        }

        return $this->render('batiment/ajouter.html.twig', [
            'form' => $form->createView(),
            'css' => 'common',
            'classItem' => "batiment",
            'routeAjouter' => "app_batiment_ajouter",
            'classSpecifique' => ""
        ]);
    }

    /**
     * Handles the deletion of one or multiple selected buildings.
     *
     * This method provides functionality to delete buildings, using the given request data
     * and session for selecting target entities, and a confirmation form to validate the operation.
     * If the form submission and confirmation are valid, the selected buildings are removed
     * and the user is redirected to the building list. Otherwise, an error is flashed.
     *
     * @param Request $request The current HTTP request containing form data and other parameters.
     * @param BatimentRepository $repository Repository for retrieving building entities.
     * @param EntityManagerInterface $entityManager Entity manager for performing database operations.
     * @param SessionInterface $session The session interface used to retrieve selected IDs.
     *
     * @return Response Rendered HTML response for the building deletion process or a redirect.
     */
    #[Route('/batiment/{id}/suppression', name: 'app_batiment_suppression')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function supprimer(Request $request, BatimentRepository $repository, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $selectedIds = $this->getSelectedIds($request, $session, 'selected_batiment');
        $batiments = $this->getSelectedBatiments($selectedIds, $repository);

        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => self::CONFIRMATION_PHRASE
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $this->isConfirmationValid($form->get('inputString')->getData())) {
            $this->deleteBatiments($batiments, $entityManager);

            return $this->redirectToRoute('app_batiment_liste');
        }

        $this->addFlash('error', 'La saisie est incorrecte.');

        return $this->render('template/supprimer.html.twig', [
            'form' => $form->createView(),
            'batiments' => $batiments,
        ]);
    }

    /**
     * Processes and updates the floors (étages) of a building.
     *
     * This method iterates through the provided list of floor names and updates
     * the corresponding floor names of the building entity if a valid name is provided.
     *
     * @param Batiment $batiment The building entity whose floors will be updated.
     * @param array $etages An associative array of floor names keyed by their respective floor identifiers.
     *
     * @return void
     */
    private function processEtages(Batiment $batiment, array $etages): void
    {
        foreach ($etages as $key => $etageName) {
            if ($etageName !== null) {
                $batiment->renameEtage($key, $etageName);
            }
        }
    }

    /**
     * Deletes a collection of buildings along with their related entities.
     *
     * This method iterates through the list of provided buildings and removes
     * all associated entities, including floors and rooms, from the database
     * using the given EntityManagerInterface. Changes are persisted after all
     * entities have been processed.
     *
     * @param array $batiments An array of building entities to be deleted.
     * @param EntityManagerInterface $entityManager The entity manager used to handle
     *                                              the deletion of entities.
     *
     * @return void
     */
    private function deleteBatiments(array $batiments, EntityManagerInterface $entityManager): void
    {
        foreach ($batiments as $batiment) {
            foreach ($batiment->getEtages() as $etage) {
                foreach ($etage->getSalles() as $salle) {
                    $entityManager->remove($salle);
                }
            }
            $entityManager->remove($batiment);
        }
        $entityManager->flush();
    }

    /**
     * Retrieves and manages selected IDs from the request or session.
     *
     * This method fetches selected IDs from the query parameters of the given request.
     * If no IDs are found in the request, it retrieves them from the session using the provided session key.
     * The selected IDs are then stored back into the session for consistency.
     *
     * @param Request $request The HTTP request containing query parameters.
     * @param SessionInterface $session The session used for storing and retrieving selected IDs.
     * @param string $sessionKey The key to identify and manage the selected IDs in the session.
     *
     * @return array An array of selected IDs retrieved from the query or session.
     */
    private function getSelectedIds(Request $request, SessionInterface $session, string $sessionKey): array
    {
        $ids = $request->query->all('selected_batiment') ?: $session->get($sessionKey, []);
        $session->set($sessionKey, $ids);

        return $ids;
    }

    /**
     * Retrieves an array of selected building entities based on a list of IDs.
     *
     * The method filters through an array of IDs, fetching the corresponding building
     * entities using the provided repository and eliminating any null results.
     *
     * @param array $ids An array of building IDs to fetch.
     * @param BatimentRepository $repository Repository used to retrieve the building entities.
     *
     * @return array A filtered array of building entities corresponding to the provided IDs.
     */
    private function getSelectedBatiments(array $ids, BatimentRepository $repository): array
    {
        return array_filter(array_map(fn($id) => $repository->find($id), $ids));
    }

    /**
     * Checks if the names of the floors in the given array are unique.
     *
     * This method compares the number of floors in the input array with the number of unique
     * elements in the array to determine if all names are distinct.
     *
     * @param array $etages An array representing the names of the floors to verify.
     *
     * @return bool True if all floor names are unique, false otherwise.
     */
    private function isNomEtagesUnique(array $etages): bool
    {
        return count($etages) === count(array_unique($etages));
    }

    /**
     * Validates if the submitted string matches the required confirmation phrase.
     *
     * This method checks whether the provided string is identical to a predefined
     * confirmation phrase stored as a class constant.
     *
     * @param string $submittedString The string to validate against the confirmation phrase.
     *
     * @return bool True if the submitted string matches the confirmation phrase, otherwise false.
     */
    private function isConfirmationValid(string $submittedString): bool
    {
        return $submittedString === self::CONFIRMATION_PHRASE;
    }

    /**
     * Persists and flushes an entity using the provided entity manager.
     *
     * This method ensures that the given entity is managed and its data is saved
     * to the database by utilizing the persist and flush operations of the entity manager.
     *
     * @param EntityManagerInterface $entityManager The entity manager responsible for handling the persistence operations.
     * @param object $entity The entity object to be persisted and flushed.
     *
     * @return void
     */
    private function persistAndFlush(EntityManagerInterface $entityManager, object $entity): void
    {
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    /**
     * Retrieves the maximum number of floors for a specific building.
     *
     * This method fetches a building entity by its ID, validates its existence,
     * and returns the maximum number of floors in JSON format. If the building
     * does not exist, a 404 exception is thrown.
     *
     * @param int $id The ID of the building to retrieve.
     * @param BatimentRepository $batimentRepository Repository used to fetch the building data.
     *
     * @return JsonResponse A JSON response containing the maximum number of floors for the building.
     *
     * @throws NotFoundHttpException If the building does not exist.
     */
    #[Route('/batiment/{id}/max-etages', name: 'batiment_max_etages', methods: ['GET'])]
    public function getMaxEtages(int $id, BatimentRepository $batimentRepository): JsonResponse
    {
        // Récupérer le bâtiment par son ID
        $batiment = $batimentRepository->find($id);

        // Si le bâtiment n'existe pas, renvoyer une erreur 404
        if (!$batiment) {
            throw new NotFoundHttpException('Bâtiment non trouvé.');
        }

        // Supposons que l'entité Batiment a une méthode getNombreEtagesMax()
        $maxEtages = $batiment->getNbEtages();

        // Retourner les données en JSON
        return new JsonResponse(['maxEtages' => $maxEtages]);
    }

    /**
     * Handles the deletion of multiple buildings as part of a selection process.
     *
     * This method retrieves the selected buildings based on user input, presents a confirmation
     * form, and deletes the entities upon successful confirmation. It also provides feedback
     * in case of incorrect inputs and renders the associated view if the process is incomplete.
     *
     * @param Request $request The current HTTP request object.
     * @param BatimentRepository $batimentRepository Repository to fetch building data by ID.
     * @param EntityManagerInterface $entityManager Entity manager for handling database modifications.
     * @param SessionInterface $session Session interface to manage session-related operations.
     *
     * @return Response The rendered view or a redirection upon successful action.
     */
    #[Route('/batiment/supprimer-selection', name: 'app_batiment_supprimer_selection', methods: ['POST', 'GET'])]


    public function suppSelection(
        Request $request,
        BatimentRepository $batimentRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        // Récupération des bâtiments sélectionnés
        $ids = $this->getSelectedBatimentIds($request, $session);
        $batiments = $this->getBatimentsByIds($ids, $batimentRepository);

        // Création du formulaire
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();

            if ($this->isConfirmationValid($submittedString)) {
                $this->deleteBatiments($batiments, $entityManager);

                return $this->redirectToRoute('app_batiment_liste');
            }

            $this->addFlash('error', 'La saisie est incorrecte.');
        }

        return $this->render('template/suppression.html.twig', [
            'form' => $form->createView(),
            'items' => $batiments,
            'classItem' => 'batiment',
            'css' => ''
        ]);
    }


    /**
     * Retrieves the list of selected building IDs from the request or session.
     *
     * This method checks if there are selected building IDs in the request. If none are found,
     * it falls back to retrieving them from the session. The retrieved IDs are then stored
     * back into the session for future reference.
     *
     * @param Request $request The HTTP request containing potential selected building IDs.
     * @param SessionInterface $session The session service to access or update stored IDs.
     *
     * @return array An array of selected building IDs.
     */
    private function getSelectedBatimentIds(Request $request, SessionInterface $session): array
    {
        $ids = $request->request->all('selected') ?: $session->get('selected', []);
        $session->set('selected', $ids);

        return $ids;
    }

    /**
     * Retrieves a list of building entities by their IDs.
     *
     * The method uses the provided repository to locate each building by its ID and
     * filters out null values to ensure only existing buildings are included in the result.
     *
     * @param array $ids An array of building IDs to retrieve.
     * @param BatimentRepository $batimentRepository Repository used to fetch building entities.
     *
     * @return array An array of building entities corresponding to the provided IDs.
     */
    private function getBatimentsByIds(array $ids, BatimentRepository $batimentRepository): array
    {
        return array_filter(
            array_map(fn($id) => $batimentRepository->find($id), $ids),
            fn($batiment) => $batiment !== null
        );
    }


}
