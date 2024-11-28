<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Form\RechercheSalleType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\PlanRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AjoutSalleType;

class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle')]
    public function index(Request $request, SalleRepository $salleRepository): Response
    {
        // Création du formulaire de recherche
        $form = $this->createForm(RechercheSalleType::class);

        // Traitement du formulaire de recherche
        $form->handleRequest($request);
        $salles = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $salleNom = $form->get('salleNom')->getData();

            // Si un nom a été saisi, on filtre les salles par le nom du bâtiment ou l'étage
            if ($salleNom) {
                // Chercher les salles dont le nom du bâtiment ou l'étage ou le numéro pourrait correspondre
                $salles = $salleRepository->findAll();

                // Filtrer les résultats avec getSalleNom() en PHP
                $salles = array_filter($salles, function($salle) use ($salleNom) {
                    return stripos($salle->getNom(), $salleNom) !== false;
                });
            }
        } else {
            // Si aucun nom n'est saisi, afficher toutes les salles
            $salles = $salleRepository->findAll();
        }

        if ($salles) {
            return $this->render('salle/index.html.twig', [
                'controller_name' => 'SalleController',
                'salles' => $salles,
                'form' => $form->createView(), // Passer le formulaire à la vue
            ]);
        } else {
            return $this->render('salle/notfound.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    }

    #[Route('/salle/{id}', name: 'app_salle_infos', requirements: ['id' => '\d+'])]
    public function infos(int $id, SalleRepository $aRepo, PlanRepository $planRepository): Response
    {
        $salle = $aRepo->find($id);
        $batiment = $salle->getBatiment();
        $plan = $planRepository->findOneBy(['salle' => $id]);

        $sa = null;
        if($plan) {
            $sa = $plan->getSA();
        }

        return $this->render('salle/infos.html.twig', [
            'salle' => $salle,
            'sa' => $sa,
        ]);
    }

    #[Route('/salle/ajout', name: 'app_salle_ajout')]
    public function ajouter(Request $request, SalleRepository $salleRepository, BatimentRepository $batimentRepository, EntityManagerInterface $entityManager): Response
    {
        $salle = new Salle();
        $form = $this->createForm(AjoutSalleType::class, $salle);

        $form->handleRequest($request);
        $batiments = $batimentRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $salleExistante = $salleRepository->findOneBy(
                ['nom' => $salle->getNom()]);
            if($salleExistante) {
                $this->addFlash('error', 'Cette salle existe déjà');
            }
            elseif($salle->getEtage() > $salle->getBatiment()->getNbEtages()) {
                $this->addFlash('error', 'Il n y a que '.$salle->getBatiment()->getNbEtages().' etages dans ce batiment');
            }
            else {
                $entityManager->persist($salle);
                $entityManager->flush();
                return $this->redirectToRoute('app_salle');
            }
        }

        return $this->render('salle/ajout.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle,
            'batiment' => $batiments,
        ]);
    }

    #[Route('/modifierSalle', name: 'app_salle_update')]
    public function modifier(Request $request, EntityManagerInterface $entityManager, SalleRepository $salleRepository, Salle $salle): Response
    {
        $salle = $salleRepository->find($request->get('salle'));
        $form = $this->createForm(AjoutSalleType::class, $salle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $salleExistante = $salleRepository->findBy(['batiment' => $salle->getBatiment(),'etage' => $salle->getEtage(), 'nom' => $salle->getNom()]);
            if($salleExistante) {
                $this->addFlash('error', 'Cette salle existe déjà');
            }
            else {
                $entityManager->persist($salle);
                $entityManager->flush();
                return $this->redirectToRoute('app_salle');
            }
        }

        return $this->render('salle/modification.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle,
        ]);
    }
    #[Route('/salle/supprimer-liees/{id}', name: 'app_salle_supprimer_liees', requirements: ['id' => '\d+'])]
    public function supprimerSallesLiees(
        int $id,
        Request $request,
        SalleRepository $salleRepository,
        BatimentRepository $batimentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer le bâtiment
        $batiment = $batimentRepository->find($id);

        // Vérifier si le bâtiment existe
        if (!$batiment) {
            $this->addFlash('error', 'Le bâtiment spécifié n\'existe pas.');
            return $this->redirectToRoute('app_batiment_liste');
        }

        // Créer le formulaire de confirmation
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => $batiment->getNom(), // Passer le nom du bâtiment comme phrase de confirmation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la saisie utilisateur
            $submittedString = $form->get('inputString')->getData();

            // Vérifier si la saisie correspond au nom du bâtiment
            if ($submittedString === $batiment->getNom()) {
                // Récupérer les salles associées
                $salles = $salleRepository->findBy(['batiment' => $batiment]);

                // Supprimer toutes les salles
                foreach ($salles as $salle) {
                    $entityManager->remove($salle);
                }
                $entityManager->flush();

                // Ajouter un message de succès
                $this->addFlash('success', 'Toutes les salles associées au bâtiment ont été supprimées.');

                // Rediriger vers la liste des bâtiments
                return $this->redirectToRoute('app_batiment_liste');
            } else {
                $this->addFlash('error', 'La saisie est incorrecte. Opération annulée.');
            }
        }

        // Afficher le formulaire
        return $this->render('salle/suppression_liees.html.twig', [
            'form' => $form->createView(),
            'batiment' => $batiment,
        ]);
    }
    #[Route('/salle/supprimer-selection', name: 'app_salle_supprimer_selection', methods: ['POST', 'GET'])]
    public function supprimerSelection(
        Request $request,
        SalleRepository $salleRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response
    {
        $ids = $request->request->all('selected_salles');
        if(empty($ids)) {
            $ids = $session->get('selected_salles', []);
        }
        else
            $session->set('selected_salles', $ids);

        $salles = array_map(fn($id) => $salleRepository->find($id), $ids);
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER' // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString=='CONFIRMER'){
                foreach ($salles as $salle ) {
                    $entityManager->remove($salle);
                }
                $entityManager->flush();
                return $this->redirectToRoute('app_salle');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('salle/suppression.html.twig', [
            'form' => $form->createView(),
            'salles' => $salles,
        ]);
    }

}