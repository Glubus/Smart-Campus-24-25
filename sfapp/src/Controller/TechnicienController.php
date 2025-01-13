<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\DetailIntervention;
use App\Entity\EtatIntervention;
use App\Form\CommentaireType;
use App\Repository\BatimentRepository;
use App\Repository\DetailInterventionRepository;
use App\Repository\SalleRepository;
use App\Service\ApiWrapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TechnicienController extends AbstractController
{

    #[Route('/technicien/accueil', name: 'app_technicien_acceuil')]
    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
    public function accueil(): Response
    {
        $technicien = $this->getUser();

        return $this->render('technicien/accueil.html.twig', [
            'technicien' => $technicien
        ]);
    }

    #[Route('/technicien/taches', name: 'app_technicien_taches')]
    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
    public function viewTaches(Request $request,
                               DetailInterventionRepository $repository,
                               FormFactoryInterface $formFactory): Response
    {
        // Récupérer l'utilisateur connecté
        $technicien = $this->getUser();

        if (!$technicien) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos tâches.');
        }

        $form = $formFactory->createBuilder()
            ->setMethod('POST')
            ->add('show_all_tasks', SubmitType::class, [
                'label' => 'Afficher toutes les tâches',
                'attr' => ['class' => 'btn btn-primary mb-4'],
            ])
            ->getForm();

        $form->handleRequest($request);

        // Récupérer les tâches de l'utilisateur connecté
            $taches = $repository->findNonTermine($technicien);

        if ($form->isSubmitted() && $form->isValid()) {
            $taches = $repository->findBy(['technicien' => $technicien]);
        }

        // Retourner la vue avec les tâches
        return $this->render('technicien/taches.html.twig', [
            'taches' => $taches,
            'form' => $form->createView(),

        ]);
    }

    #[Route('/technicien/commentaires', name: 'app_technicien_commentaire')]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function viewCommentaires(
        Request $request,
        EntityManagerInterface $entityManager,
        SalleRepository $salleRepository,
        BatimentRepository $batimentRepository,
        DetailInterventionRepository $repository,
        ApiWrapper $apiWrapper // Ajout de l'ApiWrapper pour récupérer les salles avec problèmes
    ): Response {
        $technicien = $this->getUser();

        if (!$technicien) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        $batiments = $batimentRepository->findAll();
        $taches = $repository->findAll();
        $sallesIssues = [];

        if (!empty($batiments)) {
            foreach ($batiments as $batiment) {
                // Obtenez les données des salles ayant des problèmes pour le bâtiment actuel
                $sallesIssuesData = $apiWrapper->getSallesWithIssues($batiment);

                // Ajoutez les salles problématiques au tableau principal
                $sallesIssues = array_merge($sallesIssues, $sallesIssuesData['issues']);
            }

            // Supprimez les doublons pour éviter les répétitions
            $sallesIssues = array_unique($sallesIssues);
        }

        // Déboguez pour vérifier les résultats

        $form = $this->createForm(CommentaireType::class, new DetailIntervention());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DetailIntervention $detailIntervention */
            $detailIntervention = $form->getData();
            $detailIntervention->setTechnicien($technicien);
            $detailIntervention->setDateAjout(new \DateTime());

            $entityManager->persist($detailIntervention);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');

            return $this->redirectToRoute('app_technicien_commentaire');
        }

        return $this->render('technicien/commentaires.html.twig', [
            'form' => $form->createView(),
            'technicien' => $technicien,
            'taches' => $taches,
            'sallesIssues' => $sallesIssues, // Transmettre les salles avec problèmes
            'batiments' => $batiments,
        ]);
    }



    #[Route('/technicien/commentaire/{id}', name: 'app_technicien_commentaire_ajout', methods: ['POST'])]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function addComment(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        SalleRepository $salleRepository
    ): Response {
        $technicien = $this->getUser();

        if (!$technicien || $technicien->getId() !== $id) {
            throw $this->createAccessDeniedException('Vous n’êtes pas autorisé à effectuer cette action.');
        }

        $description = $request->request->get('description');
        $salleId = $request->request->get('salle');
        $salle = $salleRepository->findByName($salleId);

        if (!$description || !$salle) {
            $this->addFlash('error', 'Les champs sont obligatoires.');
            return $this->redirectToRoute('app_technicien_commentaire');
        }

        $detailIntervention = new DetailIntervention();
        $detailIntervention->setTechnicien($technicien);
        $detailIntervention->setSalle($salle);
        $detailIntervention->setDescription($description);
        $detailIntervention->setDateAjout(new \DateTime());

        $entityManager->persist($detailIntervention);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');

        return $this->redirectToRoute('app_technicien_commentaire');
    }


    #[Route('/technicien/commentaire/supprimer/{id}', name: 'app_technicien_commentaire_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function supprimerCommentaire(
        int $id,
        DetailInterventionRepository $repository,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $commentaire = $repository->find($id);

        if (!$commentaire) {
            $this->addFlash('error', 'Commentaire introuvable.');
            return $this->redirectToRoute('app_technicien_commentaire');
        }

        $technicien = $this->getUser();

        // Vérifiez que le commentaire appartient au technicien connecté
        if ($commentaire->getTechnicien() !== $technicien) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer ce commentaire.');
            return $this->redirectToRoute('app_technicien_commentaire');
        }


        // Supprimez le commentaire
        $entityManager->remove($commentaire);
        $entityManager->flush();

        $this->addFlash('success', 'Commentaire supprimé avec succès.');
        return $this->redirectToRoute('app_technicien_commentaire');
    }





//    #[Route('/technicien/taches/{id}', name: 'app_technicien_modifer_taches')]
//    #[IsGranted('ROLE_TECHNICIEN')] // Vérifie que l'utilisateur est un technicien
//    public function modifierEtat(DetailInterventionRepository $repository,$id): Response
//    {
//        $technicien = $this->getUser();
//
//        if (!$technicien) {
//            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à vos tâches.');
//        }
//
//        // Récupérer les tâches de l'utilisateur connecté
//        $taches = $repository->findBy(['technicien' => $technicien]);
//
//        $tache = $repository->findBy(['id' => $id]);
//
//
//        // Retourner la vue avec les tâches
//        return $this->render('technicien/modifer.html.twig', [
//            'taches' => $taches,
//        ]);
//    }


}
