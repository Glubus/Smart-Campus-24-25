<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Entity\Batiment;
use App\Form\RechercheSalleType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\PlanRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Bool_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        if ($form->isSubmitted() && $form->isValid()) {
            $salleExistante = $salleRepository->findOneBy(
                ['nom' => $salle->getNom()]);
            if($salleExistante) {
                $this->addFlash('error', 'Cette salle existe déjà');
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
        ]);
    }

    #[Route('/supprSalle', name: 'app_salle_delete')]
    public function retirer(EntityManagerInterface $entityManager, Request $request, SalleRepository $salleRepository): Response
    {
        $salle = $salleRepository->find($request->get('salle'));
        if($salle) {
            $form = $this->createForm(SuppressionType::class, null, [
                'phrase' => $salle->getNom(), // Passer la variable au formulaire
            ]);
            $form->handleRequest($request);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString==$salle->getNom()){
                $entityManager->remove($salle);
                $entityManager->flush();
                return $this->redirectToRoute('app_salle');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('salle/suppression.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle,
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
}