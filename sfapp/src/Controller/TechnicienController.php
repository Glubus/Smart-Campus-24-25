<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
use App\Entity\EtatIntervention;
use App\Repository\BatimentRepository;
use App\Repository\DetailInterventionRepository;
use App\Service\ApiWrapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TechnicienController extends AbstractController
{

    /**
     * Displays the technician's homepage with diagnostic information for all buildings.
     *
     * This function retrieves all buildings from the repository and uses an API wrapper
     * to detect issues with the stations in each building. The diagnostic results are prepared
     * and displayed to the currently authenticated technician.
     *
     * @param ApiWrapper $wrapper Service used to perform diagnostics on building stations.
     * @param BatimentRepository $batimentRepository Repository used to retrieve building data.
     *
     * @return Response The rendered response containing the technician's homepage and diagnostics.
     *
     * @throws AccessDeniedException If the currently authenticated user does not have the ROLE_TECHNICIEN.
     */
    #[Route('/technicien/accueil', name: 'app_technicien_acceuil')]
    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
    public function accueil(ApiWrapper $wrapper, BatimentRepository $batimentRepository): Response
    {
        $technicien = $this->getUser();

        // Fetch all buildings
        $batiments = $batimentRepository->findAll();

        // Prepare diagnostics for each building
        $diagnostics = [];
        foreach ($batiments as $batiment) {
            $diagnostics[$batiment->getNom()] = $wrapper->detectAllStationsIssues($batiment);
        }

        dump($diagnostics);
        return $this->render('technicien/accueil.html.twig', [
            'technicien' => $technicien,
            'diagnostics' => $diagnostics,
        ]);
    }


    /**
     * Handles the display of tasks for the currently logged-in technician.
     *
     * This function retrieves the currently authenticated technician's tasks and displays them in a view.
     * Additionally, it allows the technician to filter tasks by pressing a button in the displayed form
     * to show all tasks instead of only the incomplete ones.
     *
     * @param Request $request The HTTP request object.
     * @param DetailInterventionRepository $repository Used to query tasks associated with the technician.
     * @param FormFactoryInterface $formFactory Used to create the form allowing the task filter to be toggled.
     *
     * @return Response The rendered response containing the technician's task list and form.
     *
     * @throws AccessDeniedException If the currently authenticated user is not logged in.
     */
    #[Route('/technicien/taches', name: 'app_technicien_taches')]
    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
    public function viewTaches(Request $request,
                               DetailInterventionRepository $repository,
                               FormFactoryInterface $formFactory): Response
    {
        // Récupérer l'utilisateur connecté
        $technicien = $this->getUser();

        if (!$technicien) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos tâches.');
        }

        $form = $formFactory->createBuilder()
            ->setMethod('POST')
            ->add('show_all_tasks', SubmitType::class, [
                'label' => 'Afficher toutes les tâches',
                'attr' => ['class' => 'btn btn-primary mb-4'],
            ])
            ->getForm();

        $form->handleRequest($request);

        // Récupérer les tâches de l'utilisateur connecté
            $taches = $repository->findNonTermine($technicien);

        if ($form->isSubmitted() && $form->isValid()) {
            $taches = $repository->findBy(['technicien' => $technicien]);
        }

        // Retourner la vue avec les tâches
        return $this->render('technicien/taches.html.twig', [
            'taches' => $taches,
            'form' => $form->createView(),

        ]);
    }


    /**
     * Updates the state of a task identified by its ID.
     *
     * This method validates the existence of the task and the provided state value.
     * If the task exists and the state is valid, it updates the state in the database.
     *
     * @param int $id The ID of the task to update.
     * @param Request $request The HTTP request object containing the new state data.
     * @param EntityManagerInterface $em The entity manager for database operations.
     *
     * @return JsonResponse A JSON response indicating success or an error message if the task is not found or the state is invalid.
     */
    #[Route('/api/taches/{id}/etat', name: 'update_tache_etat', methods: ['POST'])]
    public function updateEtat(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        // Vérifie que l'ID existe
        $detailIntervention = $em->getRepository(DetailIntervention::class)->find($id);
        if (!$detailIntervention) {
            return new JsonResponse(['message' => 'Tâche introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $newEtat = $data['etat'] ?? null;

        // Vérifie l'état
        if (!in_array($newEtat, array_column(EtatIntervention::cases(), 'value'))) {
            return new JsonResponse(['message' => 'État invalide'], 400);
        }

        // Mise à jour de l'état
        $detailIntervention->setEtat(EtatIntervention::from($newEtat));
        $em->persist($detailIntervention);
        $em->flush();

        return new JsonResponse(['message' => 'État mis à jour avec succès'], 200);
    }

//    #[Route('/technicien/taches/{id}', name: 'app_technicien_modifer_taches')]
//    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
//    public function modifierEtat(DetailInterventionRepository $repository,$id): Response
//    {
//        $technicien = $this->getUser();
//
//        if (!$technicien) {
//            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos tâches.');
//        }
//
//        // Récupérer les tâches de l'utilisateur connecté
//        $taches = $repository->findBy(['technicien' => $technicien]);
//
//        $tache = $repository->findBy(['id' => $id]);
//
//
//        // Retourner la vue avec les tâches
//        return $this->render('technicien/modifer.html.twig', [
//            'taches' => $taches,
//        ]);
//    }


}
