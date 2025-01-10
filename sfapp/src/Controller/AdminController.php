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
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    #[Route('/admin/assigner/{id}', name: 'app_admin_assigner', methods: ['GET', 'POST'])]
    public function assigner(Request $request, UtilisateurRepository $utilisateurRepository, DetailInterventionRepository $repository, EntityManagerInterface $entityManager,$id): Response
    {

        $intervention = new DetailIntervention();
        $form = $this->createForm(DetailInterventionType::class, $intervention);
        $form->handleRequest($request);


        $technicien = $utilisateurRepository->find($id);

        if ($form->isSubmitted() && $form->isValid()) {
            $intervention->setDateAjout(new \DateTime());
            $intervention->setTechnicien($technicien);
            $intervention->setEtat(EtatIntervention::EN_ATTENTE);
            $entityManager->persist($intervention);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/assigner.html.twig', [
            'intervention' => $intervention,
            'technicien' => $technicien,
            'form' => $form->createView(),

        ]);
    }
    #[Route('/admin/{id}', name: 'app_admin_infos')]
    public function infos($id, UtilisateurRepository $utilisateurRepository): Response
    {
        $technicien = $utilisateurRepository->find($id);

        return $this->render('admin/info.html.twig', [
            'technicien' => $technicien,
        ]);
    }
    #[Route('/admin/intervention/delete/{id}', name: 'delete_intervention', methods: ['GET'])]
    public function deleteIntervention(int $id, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Recherchez l'intervention
        $intervention = $entityManager->getRepository(DetailIntervention::class)->find($id);

        if (!$intervention) {
            // Ajouter un message d'erreur si l'intervention n'existe pas
            $this->addFlash('error', 'Intervention introuvable.');
            return $this->redirectToRoute('app_admin');
        }

        // Supprimez l'intervention
        $entityManager->remove($intervention);
        $entityManager->flush();

        // Ajouter un message de succès
        $this->addFlash('success', 'Intervention supprimée avec succès.');

        // Redirigez vers la liste des techniciens ou une autre page
        return $this->redirectToRoute('app_admin');
    }

    private function getTechnicians(UtilisateurRepository $repository, ?string $name): array
    {
        return $name
            ? $repository->findTechniciensByRoleAndNom(self::ROLE_TECHNICIEN, $name)
            : $repository->findByRole(self::ROLE_TECHNICIEN);
    }
}