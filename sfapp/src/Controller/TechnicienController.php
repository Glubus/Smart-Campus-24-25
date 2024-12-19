<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
use App\Entity\EtatIntervention;
use App\Repository\DetailInterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TechnicienController extends AbstractController
{
    #[Route('/technicien/taches', name: 'app_technicien_taches')]
    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
    public function viewTaches(DetailInterventionRepository $repository): Response
    {
        // Récupérer l'utilisateur connecté
        $technicien = $this->getUser();

        if (!$technicien) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos tâches.');
        }

        // Récupérer les tâches de l'utilisateur connecté
        $taches = $repository->findBy(['technicien' => $technicien]);

        // Retourner la vue avec les tâches
        return $this->render('technicien/taches.html.twig', [
            'taches' => $taches,
        ]);
    }


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
