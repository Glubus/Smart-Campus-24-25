<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
use App\Entity\DetailPlan;
use App\Entity\EtatIntervention;
use App\Form\DetailInterventionType;
use App\Repository\DetailInterventionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    #[Route('/new', name: 'app_detail_intervention_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DetailInterventionRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $intervention = new DetailIntervention();
        $form = $this->createForm(DetailInterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $intervention->setDateAjout(new \DateTime());
            $intervention->setEtat(EtatIntervention::EN_ATTENTE);
            $entityManager->persist($intervention); // Prépare l'entité pour la persistance
            $entityManager->flush();

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/assigner.html.twig', [
            'intervention' => $intervention,
            'form' => $form->createView(),
        ]);
    }

}
