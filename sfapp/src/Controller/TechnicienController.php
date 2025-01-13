<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
use App\Entity\EtatIntervention;
use App\Repository\DetailInterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TechnicienController extends AbstractController
{

    #[Route('/technicien/accueil', name: 'app_technicien_acceuil')]
    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
    public function accueil(): Response
    {
        $technicien = $this->getUser();

        return $this->render('technicien/accueil.html.twig', [
            'technicien' => $technicien
        ]);
    }

    /**
     * Handles the display of tasks for a technician.
     *
     * Retrieves the currently logged-in technician and fetches their pending tasks.
     * Provides a form to switch between viewing only pending tasks and all tasks.
     *
     * @param Request $request The HTTP request object.
     * @param DetailInterventionRepository $repository The repository for fetching tasks.
     * @param FormFactoryInterface $formFactory The form factory for creating the form.
     *
     * @return Response The response containing the rendered view with tasks and the form.
     *
     * @throws AccessDeniedException If the user is not authenticated as a technician.
     */
    #[Route('/technicien/taches', name: 'app_technicien_taches')]
    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
    public function viewTaches(Request                      $request,
                               DetailInterventionRepository $repository,
                               FormFactoryInterface         $formFactory): Response
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
     * Updates the state of a specific task.
     *
     * Validates the existence of the task by its ID and ensures the provided state is valid.
     * Updates the task's state and persists the changes to the database.
     *
     * @param int $id The ID of the task to update.
     * @param Request $request The HTTP request containing the new state in JSON format.
     * @param EntityManagerInterface $em The entity manager for database operations.
     *
     * @return JsonResponse The JSON response indicating success or an error message.
     */
    #[Route('/api/taches/{id}/etat', name: 'update_tache_etat', methods: ['POST'])]
    public function updateEtat(
        int                    $id,
        Request                $request,
        EntityManagerInterface $em
    ): JsonResponse
    {
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
}