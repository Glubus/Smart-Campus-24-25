<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Form\AjoutBatimentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BatimentController extends AbstractController
{
    #[Route('/batiment', name: 'app_batiment')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupération de tous les bâtiments depuis la base de données
        $batiments = $em->getRepository(Batiment::class)->findAll();

        return $this->render('batiment/index.html.twig', [
            'batiments' => $batiments,
        ]);
    }

    #[Route('/batiment/liste', name: 'app_batiment_liste')]
    public function liste(EntityManagerInterface $em): Response
    {
        // Récupérer la liste des bâtiments
        $batiments = $em->getRepository(Batiment::class)->findAll();

        return $this->render('batiment/liste.html.twig', [
            'batiments' => $batiments,
        ]);
    }

    #[Route('/batiment/ajouter', name: 'app_batiment_ajouter')]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        // Initialisation d'un nouveau bâtiment
        $batiment = new Batiment();

        // Création du formulaire
        $form = $this->createForm(AjoutBatimentType::class, $batiment);

        // Gestion de la requête
        $form->handleRequest($request);

        // Vérification de la soumission et de la validation
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($batiment);
            $em->flush();

            // Message flash pour confirmer l'ajout
            $this->addFlash('success', 'Bâtiment ajouté avec succès.');

            // Redirection vers la liste des bâtiments après ajout
            return $this->redirectToRoute('app_batiment_liste');
        }

        return $this->render('batiment/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
