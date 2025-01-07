<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
use App\Entity\DetailPlan;
use App\Entity\EtatIntervention;
use App\Form\DetailInterventionType;
use App\Form\RechercheSaType;
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
    public function index(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {

        $form = $this->createForm(RechercheSaType::class);
        $form->handleRequest($request);

        // Check if the form is submitted and valid, then filter results
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();


            dump($data);
            // Filter the `SA` entities based on the search term
            if (!empty($data['nom'])) {
                // Recherche avec filtre par nom
                $techniciens = $utilisateurRepository->findTechniciensByRoleAndNom('ROLE_TECHNICIEN', $data['nom']);
            }
        } else {
            // If no filtering, get all entities
            $techniciens = $utilisateurRepository->findByRole('ROLE_TECHNICIEN');
        }

        return $this->render('admin/liste.html.twig', [
            'controller_name' => 'AdminController',
            'techniciens' => $techniciens,
            'form' => $form->createView()
        ]);
    }
    #[Route('/admin/assigner/{id}', name: 'app_admin_assigner', methods: ['GET', 'POST'])]
    public function new(Request $request, UtilisateurRepository $utilisateurRepository, DetailInterventionRepository $repository, EntityManagerInterface $entityManager,$id): Response
    {

        $intervention = new DetailIntervention();
        $form = $this->createForm(DetailInterventionType::class, $intervention);
        $form->handleRequest($request);


        $technicien = $utilisateurRepository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            $intervention->setDateAjout(new \DateTime());
            $intervention->setTechnicien($technicien);
            $intervention->setEtat(EtatIntervention::EN_ATTENTE);
            $entityManager->persist($intervention); // Prépare l'entité pour la persistance
            $entityManager->flush();

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/assigner.html.twig', [
            'intervention' => $intervention,
            'technicien' => $technicien,
            'form' => $form->createView(),

        ]);
    }

}
