<?php

namespace App\Controller;

use App\Entity\ActionLog;
use App\Entity\Commentaires;
use App\Entity\SA;
use App\Entity\DetailPlan;
use App\Entity\SALog;
use App\Entity\TypeCapteur;
use App\Form\AjoutSAType;
use App\Form\RechercheSaType;
use App\Form\SuppressionType;
use App\Repository\CommentairesRepository;
use App\Repository\SARepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\VarDumper;

class SAController extends AbstractController
{
    #[Route('/sa/modifier/{id}', name: 'app_sa_modifier', requirements: ['id' => '\d+'])]
    public function modifier(int $id, SARepository $SARepository, EntityManagerInterface $entityManager,Request $request): Response
    {
        $SA=$SARepository->find($id);
        if ($SA==null) {
            return new Response('Page Not Found', 404);
        }
        $form = $this->createForm(AjoutSAType::class, $SA);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {

            if (count($SARepository->findBy(["nom" => $SA->getNom()], ["nom" => "ASC"]))>0){
                $this->addFlash('error', 'Le nom saisi est déjà utilisé.');
            }
            else{
                $LogCrea=new SALog();
                $LogCrea->setSA($SA);

                $LogCrea->setDate(new \DateTime());
                $LogCrea->setAction(ActionLog::MODIFIER);
                $entityManager->persist($LogCrea);
                    $entityManager->flush();

                return $this->redirectToRoute('app_sa_liste');
            }
        }
        // Affichage du formulaire
        return $this->render('sa/ajouter.html.twig', [
            'form' => $form->createView(),
            'css' => 'sa',
            'classItem' => "sa",
            'routeItem'=> "app_sa_modifier",
            'classSpecifique' => ""
        ]);
    }
    #[Route('/sa/ajouter', name: 'app_sa_ajouter')]
    public function ajoutSA(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sa = new SA();

        $form = $this->createForm(AjoutSAType::class, $sa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($entityManager->getRepository(SA::class)->findBy(["nom" => $sa->getNom()], ["nom" => "ASC"]))>0){
                $this->addFlash('error', 'Le nom saisi est déjà utilisé.');
            }
            else {
                $entityManager->persist($sa);
                $entityManager->flush();

                $this->addFlash('success', 'SA ajouté avec succès.');

                return $this->redirectToRoute('app_sa_liste');
            }
        }

        return $this->render('sa/ajouter.html.twig', [
            'form' => $form->createView(),
            'css' => 'sa',
            'classItem' => "sa",
            'routeItem'=> "app_sa_ajouter",
            'classSpecifique' => ""
        ]);
    }

    #[Route('/sa', name: 'app_sa_liste')]
    public function lister(SARepository $saRepo, Request $request): Response
    {
        // Create the form for searching
        $form = $this->createForm(RechercheSaType::class);
        $form->handleRequest($request);

        // Check if the form is submitted and valid, then filter results
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Filter the `SA` entities based on the search term
            $sa = $saRepo->findByNomSA($data['nom']);
        } else {
            // If no filtering, get all entities
            $sa = $saRepo->findAll();
        }

        // Render the page with the form and filtered results
        return $this->render('sa/liste.html.twig', [
            'css' => 'sa',
            'classItem' => "sa",
            'items' => $sa,
            'routeItem'=> "app_sa_ajouter",
            'classSpecifique' => "",
            'form' => $form->createView()
        ]);
    }
    #[Route('/sa/{id}/suppression', name: 'app_sa_suppression')]
    public function supprimer(Request $request, int $id, SARepository $repo, EntityManagerInterface $em): Response
    {
        $SA = $repo->find($id);
        if ($SA) {
            $form = $this->createForm(SuppressionType::class, null, [
                'phrase' => $SA->getNom(), // Passer la variable au formulaire
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $submittedString = $form->get('inputString')->getData();
                if ($submittedString == $SA->getNom()) {
                    $em->remove($SA);
                    $em->flush();
                    $this->addFlash('success', 'SA supprimé avec succès.');
                    return $this->redirectToRoute('app_sa_liste');
                } else {
                    $this->addFlash('error', 'La saisie est incorrecte.');
                }
            }

            return $this->render('sa/supprimer.html.twig', [
                "form" => $form->createView(),
                "SA" => $SA,
            ]);
        }

        return $this->render('sa/notfound.html.twig', []);
    }


    #[Route('/sa/{id}', name: 'app_sa_infos', requirements: ['id' => '\d+'])]
    public function affichage_SA(Request $request, int $id, SARepository $repo,EntityManagerInterface $entityManager, CommentairesRepository $commentairesRepository): Response
    {
        $SA = $repo->find($id);
        $commentaires = $commentairesRepository->findBy(['SA' => $SA], ['dateAjout' => 'DESC'], 5);

        if (!$SA) {
            throw $this->createNotFoundException('SA introuvable.');
        }
        $histo=$SA->getSALogs();
        // trouver la salle d'un Sa
        $plan = $entityManager->getRepository(DetailPlan::class)->findOneBy(['sa' => $SA]);
        $salle = $plan ? $plan->getSalle() : null;
        dump($commentaires);

        return $this->render('sa/info.html.twig', [
            "SA" => $SA,
            "salle" => $salle,
            "histo" => $histo,
            'commentaires' => $commentaires,
        ]);
    }


    #[Route('/sa/{id}/commentaire', name :'app_sa_commentaire')]
    public function ajouterCommentaire(int $id,Request $request,EntityManagerInterface $entityManager,SARepository $SARepository): Response {
        // Récupérer l'entité SA
        $SA = $SARepository->find($id);

        // Récupérer la description du commentaire
        $description = $request->request->get('description');
        $nomTech = $request->request->get('nomTech');

        // Créer et associer le commentaire
        $commentaire = new Commentaires();
        $commentaire->setNomTech($nomTech);
        $commentaire->setDescription($description);
        $commentaire->setSA($SA);

        // Persist le commentaire
        $entityManager->persist($commentaire);
        $entityManager->flush();

        // Rediriger vers la page du SA
        return $this->redirectToRoute('app_sa_infos', ['id' => $id]);
    }
    #[Route('/sa/{id}/commentaire/{commentaireId}/supprimer', name: 'app_sa_commentaire_supprimer')]
    public function supprimerCommentaire(
        int $id,
        int $commentaireId,
        EntityManagerInterface $entityManager,
        SARepository $SARepository,
        CommentairesRepository $commentairesRepository
    ): Response {
        // Récupérer l'entité SA
        $SA = $SARepository->find($id);
        if (!$SA) {
            throw $this->createNotFoundException("L'entité SA n'a pas été trouvée.");
        }

        // Récupérer le commentaire à supprimer
        $commentaire = $commentairesRepository->find($commentaireId);
        if (!$commentaire) {
            throw $this->createNotFoundException("Le commentaire n'a pas été trouvé.");
        }

        // Vérifier si le commentaire appartient bien à l'entité SA
        if ($commentaire->getSA() !== $SA) {
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à supprimer ce commentaire.");
        }

        // Supprimer le commentaire
        $entityManager->remove($commentaire);
        $entityManager->flush();

        // Ajouter un message flash pour informer l'utilisateur
        $this->addFlash('success', "Le commentaire a été supprimé avec succès.");

        // Rediriger vers la page de l'entité SA après suppression
        return $this->redirectToRoute('app_sa_infos', ['id' => $id]);
    }


    #[Route('/sa/log/{id}', name: 'app_sa_log')]
    public function affichage_log_sa(Request $request, int $id, SARepository $repo,): Response
    {
        $SA = $repo->find($id);
        if (!$SA) {
            throw $this->createNotFoundException('SA introuvable.');
        }
        $histo=$SA->getSALogs();

        return $this->render('sa/historique.html.twig', [
            "histo" => $histo,
        ]);
    }


    #[Route("/sa/{id}/commentaires-ajax", name: 'app_sa_commentaires_ajax')]
    public function commentairesAjax(Sa $SA, Request $request, CommentairesRepository $commentairesRepository): JsonResponse
    {
        $offset = (int) $request->query->get('offset', 0);

        // Récupérer les commentaires associés
        $commentaires = $commentairesRepository->findBy(
            ['sa' => $SA],
            ['dateAjout' => 'DESC'],
            5,
            $offset
        );

        // Si aucun commentaire n'est trouvé
        if (!$commentaires) {
            return new JsonResponse([], 200);
        }

        // Préparer les données pour JSON
        $data = array_map(fn($commentaire) => [
            'id' => $commentaire->getId(),
            'nomTech' => $commentaire->getNomTech(),
            'dateAjout' => $commentaire->getDateAjout()->format('d/m/Y'),
            'description' => $commentaire->getDescription(),
        ], $commentaires);

        return new JsonResponse($data, 200);
    }


    #[Route('/sa/supprimer-selection', name: 'app_sa_supprimer_selection', methods: ['POST', 'GET'])]
    public function suppSelection(
        Request $request,
        SaRepository $saRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response
    {
        $ids = $request->request->all('selected_sa');
        if(empty($ids)) {
            $ids = $session->get('selected_sa', []);
        }
        else
            $session->set('selected_sa', $ids);

        $sa = array_map(fn($id) => $saRepository->find($id), $ids);
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER' // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString=='CONFIRMER'){

                foreach ($sa as $sas) {
                    foreach ($sas->getPlans() as $plan) {
                        $entityManager->remove($plan);
                    }
                    // Remove related SALog entries
                    foreach ($sas->getSALogs() as $log) {
                        $entityManager->remove($log);
                    }
                    foreach ($sas->getValCapteurs() as $valCapteur) {
                        $entityManager->remove($valCapteur);
                    }
                    // Remove the SA entity
                    $entityManager->remove($sas);
                }
                $entityManager->flush();

                return $this->redirectToRoute('app_sa_liste');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('sa/suppression_sa.html.twig', [
            'form' => $form->createView(),
            'sa' => $sa,
        ]);
    }
}
