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

    /*
     * Relier a l'US15 : En tant que chargé de projet, je souhaite ajouter un batiment pour mettre des salles
     * But : lister salle
     * @param :  BatimentRepository pour fetchAll
     * @return : renvois une page twig avec la liste des batiments afficher
     * @route : "/batiment"
     * @name : app_batiment_liste
     */
    #[Route('/batiment', name: 'app_batiment_liste')]
    public function liste(BatimentRepository $repo): Response
    {
        // Récupérer la liste des bâtiments
        $batiments = $repo->findAll();

        return $this->render('batiment/liste.html.twig', [
            'batiments' => $batiments,
        ]);
    }

    /*
     * Relier a l'US15 : En tant que chargé de projet, je souhaite ajouter un batiment pour mettre des salles
     * But : ajouter une salle
     * @param :  BatimentRepository pour find en cas de modif, Request pour le form et EntityManagerInterface pour persist et flush dans la DB
     * @return : renvois une page twig pour ajouter un batimejnt
     * @route : "/batiment/ajouter"
     * @name : app_batiment_liste
     */
    #[Route('/batiment/ajouter', name: 'app_batiment_ajouter')]
    public function ajouter(Request $request, BatimentRepository $batimentRepository, EntityManagerInterface $em): Response
    {
        // Definition des variables utiliser dans la fonction

        $batiment=null; // Mettre l'entité batiment dedans
        $form=null; // Mettre le form dedans
        $req=null; // sert a recuperer le batiment de la requete si il existe

        $req=$request->get('batiment');

        // si la requete existe alors find
        if ($req) {
            $batiment = $batimentRepository->find($req);
        }

        // si le find est null alors c'est une nouvelle insertion
        if (!$batiment) {
            // Initialisation d'un nouveau bâtiment
            $batiment = new Batiment();
        }

        // Création du formulaire
        $form = $this->createForm(AjoutBatimentType::class, $batiment);

        // Gestion de la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // on regarde si il existe pas déjà
            $batimentExistante = $batimentRepository->findBy(
                ['nom' => $batiment->getNom()]);
            if($batimentExistante) {
                $this->addFlash('error', 'Ce batiment existe déjà');
            }
            else{
                // sinon on update la db
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



}
