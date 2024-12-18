<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\Salle;
use App\Entity\DetailPlan;
use App\Entity\SA;
use App\Entity\Utilisateur;
use App\Form\ajoutBatimentType;
use App\Form\ajoutSalleType;
use App\Form\AssociationSASalle;
use App\Form\ajoutSAType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GestionController extends AbstractController
{
    #[Route('/gestion', name: 'app_gestion')]
    public function gestion(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer les entités
        $batiment = new Batiment();
        $salle = new Salle();
        $plan = new DetailPlan();
        $sa = new SA();

        // Créer les formulaires à partir des types existants
        $batimentForm = $this->createForm(ajoutBatimentType::class, $batiment);
        $salleForm = $this->createForm(ajoutSalleType::class, $salle);
        $planForm = $this->createForm(AssociationSAsalle::class, $plan);
        $saForm = $this->createForm(ajoutSAType::class, $sa);

        // Gérer la soumission de chaque formulaire
        $batimentForm->handleRequest($request);
        $salleForm->handleRequest($request);
        $planForm->handleRequest($request);
        $saForm->handleRequest($request);

        // Vérifier chaque formulaire et persister si nécessaire
        if ($batimentForm->isSubmitted() && $batimentForm->isValid()) {
            $entityManager->persist($batiment);
            $entityManager->flush();
            $this->addFlash('success', 'Bâtiment ajouté avec succès !');
        }

        if ($salleForm->isSubmitted() && $salleForm->isValid()) {
            $entityManager->persist($salle);
            $entityManager->flush();
            $this->addFlash('success', 'Salle ajoutée avec succès !');
        }

        if ($planForm->isSubmitted() && $planForm->isValid()) {
            $entityManager->persist($plan);
            $entityManager->flush();
            $this->addFlash('success', 'DetailPlan ajouté avec succès !');
        }

        if ($saForm->isSubmitted() && $saForm->isValid()) {
            $entityManager->persist($sa);
            $entityManager->flush();
            $this->addFlash('success', 'Système d\'acquisition ajouté avec succès !');
        }

        // Rendu du template avec tous les formulaires
        return $this->render('gestion/index.html.twig', [
            'batimentForm' => $batimentForm->createView(),
            'salleForm' => $salleForm->createView(),
            'planForm' => $planForm->createView(),
            'saForm' => $saForm->createView(),
        ]);
    }
    #[Route('/admin/technicien', name: 'app_technicien_liste')]
    public function gestion_technicien(Request $request, UtilisateurRepository $entityManager): Response
    {
        $items = $entityManager->findByRole('ROLE_USER');

        return $this->render('gestion/liste.html.twig', [
            'css' => 'technicien',
            'classItem' => "technicien",
            'items' => $items,
            'routeItem'=> "app_batiment_ajouter",
            'classSpecifique' => ""
        ]);
    }
    #[Route('/admin/technicien/ajouter', name: 'app_technicien_ajouter')]
    public function ajouter_technicien(): Response
    {

        return $this->redirectToRoute("app_register");
    }
    #[Route('/admin/technicien/{id}', name: 'app_technicien_infos')]
    public function infos(int $id, UtilisateurRepository $repository): Response
    {
        $user=$repository->find($id);
        $interventions = $user->getDetailInterventions();
        return $this->render('gestion/infos.html.twig', [
            'css' => 'technicien',
            'classItem' => "technicien",
            'item' => $user,
            'routeItem'=> "app_technicien_ajouter",
            'interventions' => $interventions,
            'classSpecifique' => ""
        ]);
    }

    #[Route('/admin/technicien/supprimer', name: 'app_technicien_supprimer_selection')]
    public function supprimer(): Response
    {

        return $this->render('batiment/infos.html.twig', []);
    }
}
