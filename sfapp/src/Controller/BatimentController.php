<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\SA;
use App\Entity\Plan;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\DetailPlan;
use App\Form\AjoutBatimentType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
        BatimentRepository $batimentRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        // Récupérer les IDs à partir de la requête GET
        $ids = $request->query->all('selected_batiment');

        if (empty($ids)) {
            $ids = $session->get('selected_batiment', []);
        } else {
            $session->set('selected_batiment', $ids);
        }

        $batiments = array_map(fn($id) => $batimentRepository->find($id), $ids);

        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER' // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();

            if ($submittedString === 'CONFIRMER') {
                foreach ($batiments as $batiment) {
                    foreach ($batiment->getSalles() as $salle) {
                        $entityManager->remove($salle);
                    }
                    $entityManager->remove($batiment);
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_batiment_liste');
            } else {
                $this->addFlash('error', 'La saisie est incorrecte.');
            }
        }

        return $this->render('batiment/suppression.html.twig', [
            "form" => $form->createView(),
            "batiment" => $batiments,
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
    #[Route('/batiment/supprimer-selection', name: 'app_batiment_supprimer_selection', methods: ['POST', 'GET'])]
    public function suppSelection(
        Request $request,
        BatimentRepository $batimentRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        // Fetch the 'selected_batiments' from the request
        $ids = $request->request->all('selected_batiments');

        if (empty($ids)) {
            $ids = $session->get('selected_batiments', []);
        } else {
            $session->set('selected_batiments', $ids);
        }

        $batiments = array_map(fn($id) => $batimentRepository->find($id), $ids);

        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();

            if ($submittedString === 'CONFIRMER') {

                if (!is_iterable($batiments)) {
                    throw new \Exception("No buildings found.");
                }
                foreach ($batiments as $batiment) {


                    $plans = $entityManager->getRepository(Plan::class)->findBy(['Batiment' => $ids]);



                    foreach ($plans as $plan) {
                        foreach ($plan->getDetailPlans() as $detailPlan) {
                            $detailPlan->setPlan(null); // Détache le détail du plan
                            $entityManager->persist($detailPlan); // Persiste le détail du plan
                        }
                        $entityManager->remove($plan);
                    }


                    $salles = $batiment->getSalles();
                    foreach ($salles as $salle) {
                        $sas = $entityManager->getRepository(SA::class)->findBy(['salle' => $salle]);
                        foreach ($sas as $sa) {
                            $sa->setSalle(null);
                            $entityManager->persist($sa); // Persist pour enregistrer les modifications
                        }
                        $detailPlans = $salle->getDetailPlans();
                        $valeurCapteurs = $salle->getValeurCapteurs();

                        foreach ($detailPlans as $detailPlan) {
                            $entityManager->remove($detailPlan);
                        }

                        foreach ($valeurCapteurs as $valeurCapteur) {
                            $entityManager->remove($valeurCapteur);
                        }

                        $entityManager->remove($salle);
                    }
                    $entityManager->remove($batiment);
                }
                $entityManager->flush();
                return $this->redirectToRoute('app_batiment_liste');
            }
                else {
                $this->addFlash('error', 'La saisie est incorrecte.');
            }
        }

        return $this->render('batiment/suppression_batiment.html.twig', [
            'form' => $form->createView(),
            'batiments' => $batiments,
        ]);
    }


}
