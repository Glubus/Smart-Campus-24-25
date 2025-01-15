<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
use App\Entity\EtatIntervention;
use App\Repository\BatimentRepository;
use App\Repository\DetailInterventionRepository;
use App\Repository\SalleRepository;
use App\Service\ApiWrapper;
use App\Form\CommentaireType;
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
    #[Route('/technicien', name: 'app_technicien_acceuil')]
    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
    public function accueil(
        Request $request,
        ApiWrapper $wrapper,
        EntityManagerInterface $entityManager,
        BatimentRepository $batimentRepository,
        SalleRepository $salleRepository,
        DetailInterventionRepository $repository,
        ApiWrapper $apiWrapper // Ajout de l'ApiWrapper pour récupérer les salles avec problèmes
    ): Response {
        $technicien = $this->getUser();

        if (!$technicien) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Fetch all buildings
        $batiments = $batimentRepository->findAll();
        $taches = $repository->findBy(
            ['technicien' => $technicien], // Filtrage par technicien
            ['dateAjout' => 'DESC'],        // Tri par date d'ajout, décroissant
        );


        // Prepare diagnostics for each building
        $diagnostics = [];
        foreach ($batiments as $batiment) {
            $diagnostics[$batiment->getNom()] = $wrapper->detectAllStationsIssues($batiment);
        }

        // Récupérer les salles avec problèmes
        $sallesIssues = [];
        if (!empty($batiments)) {
            foreach ($batiments as $batiment) {
                // Obtenez les données des salles ayant des problèmes pour le bâtiment actuel
                $sallesIssuesData = $apiWrapper->getSallesWithIssues($batiment);

                // Ajoutez les salles problématiques au tableau principal
                $sallesIssues = array_merge($sallesIssues, $sallesIssuesData['issues']);
            }

            // Supprimez les doublons pour éviter les répétitions
            $sallesIssues = array_unique($sallesIssues);
        }

        // Créer le formulaire de commentaire
        $form = $this->createForm(CommentaireType::class, new DetailIntervention());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DetailIntervention $detailIntervention */
            $detailIntervention = $form->getData();
            $detailIntervention->setTechnicien($technicien);
            $detailIntervention->setDateAjout(new \DateTime());

            $entityManager->persist($detailIntervention);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');

            return $this->redirectToRoute('app_technicien_acceuil');
        }

        return $this->render('technicien/accueil.html.twig', [
            'technicien' => $technicien,
            'diagnostics' => $diagnostics,
            'taches' => $taches,
            'sallesIssues' => $sallesIssues, // Transmettre les salles avec problèmes
            'batiments' => $batiments,

            'form' => $form->createView(), // Formulaire pour ajouter un commentaire
        ]);
    }



    #[Route('/technicien/commentaire/{id}', name: 'app_technicien_commentaire_ajout', methods: ['POST'])]
    #[Security("is_granted('ROLE_TECHNICIEN') or is_granted('ROLE_CHARGE_DE_MISSION')")]
    public function addComment(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        SalleRepository $salleRepository
    ): Response {
        $technicien = $this->getUser();

        if (!$technicien || $technicien->getId() !== $id) {
            throw $this->createAccessDeniedException('Vous n’êtes pas autorisé à effectuer cette action.');
        }

        $description = $request->request->get('description');
        $salleId = $request->request->get('salle');
        $salle = $salleRepository->findByName($salleId);

        if (!$description || !$salle) {
            $this->addFlash('error', 'Les champs sont obligatoires.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_technicien_acceuil'));
        }

        $detailIntervention = new DetailIntervention();
        $detailIntervention->setTechnicien($technicien);
        $detailIntervention->setSalle($salle);
        $detailIntervention->setDescription($description);
        $detailIntervention->setDateAjout(new \DateTime());

        $entityManager->persist($detailIntervention);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');

        // Rediriger vers la page précédente
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_technicien_acceuil'));
    }



    #[Route('/technicien/commentaire/supprimer/{id}', name: 'app_technicien_commentaire_supprimer', methods: ['POST'])]
    #[Security("is_granted('ROLE_TECHNICIEN') or is_granted('ROLE_CHARGE_DE_MISSION')")]
    public function supprimerCommentaire(
        int $id,
        DetailInterventionRepository $repository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $commentaire = $repository->find($id);

        if (!$commentaire) {
            $this->addFlash('error', 'Commentaire introuvable.');
            return $this->redirectToRoute('app_technicien_acceuil');
        }

        $technicien = $this->getUser();

        // Vérifiez que le commentaire appartient au technicien connecté
        if ($commentaire->getTechnicien() !== $technicien or $this->isGranted('ROLE_CHARGE_DE_MISSION')) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer ce commentaire.');
            return $this->redirectToRoute('app_technicien_acceuil');
        }


        // Supprimez le commentaire
        $entityManager->remove($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire supprimé avec succès.');
        return $this->redirectToRoute('app_technicien_acceuil');
    }


    #[Route("/taches/{id}/ajax", name: 'app_taches_ajax')]
    public function tachesAjax(
        int $id,
        Request $request,
        DetailInterventionRepository $detailInterventionRepository
    ): JsonResponse {

        $offset = (int) $request->query->get('offset', 0);
        try {
            // Récupérer le technicien connecté
            $technicien = $this->getUser();

            // Vérifier que l'utilisateur est bien connecté et correspond au technicien avec l'ID
            if (!$technicien || $technicien->getId() !== $id) {
                return new JsonResponse(['error' => 'Accès refusé.'], 403);
            }

            // Récupérer les tâches pour le technicien donné, avec la pagination
            $taches = $detailInterventionRepository->findTachesByTechnicienWithPagination(
                5, // Limite de résultats
                $offset // Offset pour la pagination
            );


            if (!$taches) {
                return new JsonResponse(['message' => 'Aucune tâche trouvée.'], 404);
            }

            // Préparation des données JSON pour les tâches
            $data = array_map(fn($tache) => [
                'id' => $tache->getId(),
                'nomTechnicien' => $tache->getTechnicien()->getNom(),
                'dateAjout' => $tache->getDateAjout()->format('Y-m-d H:i'),
                'salleNom' => $tache->getSalle()->getNom(),
                'description' => $tache->getDescription(),
            ], $taches);

            return new JsonResponse($data, 200);
        } catch (\Exception $e) {
            // Log de l'erreur
            error_log('Erreur lors de la récupération des tâches : ' . $e->getMessage());
            return new JsonResponse(['error' => 'Une erreur est survenue.'], 500);
        }
    }

}
