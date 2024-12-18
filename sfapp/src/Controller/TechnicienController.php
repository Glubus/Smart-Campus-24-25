<?php

namespace App\Controller;

use App\Repository\DetailInterventionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
