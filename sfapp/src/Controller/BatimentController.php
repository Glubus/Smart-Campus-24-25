<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Form\AjoutBatimentType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BatimentController extends AbstractController
{
    /*#[Route('/batiment', name: 'app_batiment')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupération de tous les bâtiments depuis la base de données
        $batiments = $em->getRepository(Batiment::class)->findAll();

        return $this->render('batiment/ajouter.html.twig', [
            'batiments' => $batiments,
        ]);
    }*/

    #[Route('/batiment', name: 'app_batiment_liste')]
    public function liste(EntityManagerInterface $em): Response
    {
        // Récupérer la liste des bâtiments
        $batiments = $em->getRepository(Batiment::class)->findAll();

        return $this->render('batiment/liste.html.twig', [
            'batiments' => $batiments,
        ]);
    }

    #[Route('/batiment/ajout', name: 'app_batiment_ajouter')]
    public function ajouter(Request $request, BatimentRepository $batimentRepository, EntityManagerInterface $em): Response
    {
        $req=$request->get('batiment');
        $batiment=null;
        if ($req) {
            $batiment = $batimentRepository->find($req);
        }
        if (!$batiment) {
            // Initialisation d'un nouveau bâtiment
            $batiment = new Batiment();
        }
        // Création du formulaire
        $form = $this->createForm(AjoutBatimentType::class, $batiment);

        // Gestion de la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $batimentExistante = $batimentRepository->findBy(
                ['nom' => $batiment->getNom()]);
            if($batimentExistante
            ) {
                $this->addFlash('error', 'Ce batiment existe déjà');
            }
            else{
                $em->persist($batiment);
                $em->flush();

                // Redirection vers la liste des bâtiments après ajout
                return $this->redirectToRoute('app_batiment_liste');
                }
        }

        return $this->render('batiment/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/batiment/{id}/suppression', name: 'app_batiment_suppression')]
    public function supprimer(
        Request $request,
        int $id,
        BatimentRepository $repo,
        SalleRepository $salleRepo,
        EntityManagerInterface $em
    ): Response {
        $batiment = $repo->find($id);

        // Vérification si le bâtiment existe
        if (!$batiment) {
            return $this->render('batiment/notfound.html.twig', []);
        }

        // Vérification des salles associées
        $sallesAssociees = $salleRepo->findBy(['batiment' => $batiment]);
        if (!empty($sallesAssociees)) {
            // Ajouter un message d'erreur et rediriger
            $this->addFlash('error', [  'message' => 'Impossible de supprimer ce bâtiment car des salles y sont associées.',
                'batimentId' => $batiment->getId(),]);
            return $this->redirectToRoute('app_batiment_liste');
        }

        // Création du formulaire
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => $batiment->getNom(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString === $batiment->getNom()) {
                // Suppression du bâtiment
                $em->remove($batiment);
                $em->flush();
                $this->addFlash('success', 'Bâtiment supprimé avec succès.');
                return $this->redirectToRoute('app_batiment_liste');
            } else {
                $this->addFlash('error', 'La saisie est incorrecte.');
            }
        }

        return $this->render('batiment/suppression.html.twig', [
            "form" => $form->createView(),
            "batiment" => $batiment,
        ]);
    }
    #[Route('/batiment/{id}/max-etages', name: 'batiment_max_etages', methods: ['GET'])]
    public function getMaxEtages(int $id, BatimentRepository $batimentRepository): JsonResponse
    {
        // Récupérer le bâtiment par son ID
        $batiment = $batimentRepository->find($id);

        // Si le bâtiment n'existe pas, renvoyer une erreur 404
        if (!$batiment) {
            throw new NotFoundHttpException('Bâtiment non trouvé.');
        }

        // Supposons que l'entité Batiment a une méthode getNombreEtagesMax()
        $maxEtages = $batiment->getNbEtages();

        // Retourner les données en JSON
        return new JsonResponse(['maxEtages' => $maxEtages]);
    }


}
