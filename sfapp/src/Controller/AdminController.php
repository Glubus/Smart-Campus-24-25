<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
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

class  AdminController extends AbstractController
{
    private const ROLE_TECHNICIEN = 'ROLE_TECHNICIEN';

    #[Route('/admin', name: 'app_admin')]
    public function index(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        $form = $this->createForm(RechercheSaType::class);
        $form->handleRequest($request);

        $formData = $form->isSubmitted() && $form->isValid() ? $form->getData() : null;
        $filteredTechniciens = $this->getTechnicians($utilisateurRepository, $formData['nom'] ?? null);

        return $this->render('admin/liste.html.twig', [
            'controller_name' => 'AdminController',
            'techniciens' => $filteredTechniciens,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_detail_intervention_new', methods: ['GET', 'POST'])]
    public function new(
        Request                      $request,
        DetailInterventionRepository $repository,
        EntityManagerInterface       $entityManager
    ): Response
    {
        $intervention = new DetailIntervention();
        $form = $this->createForm(DetailInterventionType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $intervention->setDateAjout(new \DateTime());
            $intervention->setEtat(EtatIntervention::EN_ATTENTE);
            $entityManager->persist($intervention);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/assigner.html.twig', [
            'intervention' => $intervention,
            'form' => $form->createView(),
        ]);
    }

    private function getTechnicians(UtilisateurRepository $repository, ?string $name): array
    {
        return $name
            ? $repository->findTechniciensByRoleAndNom(self::ROLE_TECHNICIEN, $name)
            : $repository->findByRole(self::ROLE_TECHNICIEN);
    }
}