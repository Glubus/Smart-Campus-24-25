<?php

namespace App\Controller;

use App\Entity\EtageSalle;
use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanController extends AbstractController
{
    #[Route('/plan', name: 'plan')]
    public function index(PlanRepository $planRepository): Response
    {
        // Récupérer toutes les salles du rez-de-chaussée
        $sallesRezDeChaussee = $planRepository->findSallesRezDeChaussee();
        $count=count($sallesRezDeChaussee);
        // Passer les salles au template
        return $this->render('plan/index.html.twig', [
            'salles' => $sallesRezDeChaussee,
            'count' => $count,
        ]);
    }
}
