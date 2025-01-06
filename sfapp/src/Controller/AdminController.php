<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
use App\Entity\DetailPlan;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Utilisateur;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        $techniciens = $utilisateurRepository->findByRole('ROLE_TECHNICIEN');

        return $this->render('admin/liste.html.twig', [
            'controller_name' => 'AdminController',
            'techniciens' => $techniciens,
        ]);
    }

}
