<?php

namespace App\Controller;

use App\Entity\ActionLog;
use App\Entity\Batiment;
use App\Entity\Plan;
use App\Entity\SALog;
use App\Form\AjoutPlanType;
use App\Form\AjoutSAType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\PlanRepository;
use App\Repository\SARepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class PlanController extends AbstractController
{


    #[Route('/plan', name: 'app_plan_liste')]
    public function index(PlanRepository $repo): Response
    {
        $plan=$repo->findAll();
        return $this->render('plan/liste.html.twig', [
            'css' => 'plan',
            'classItem' => "plan",
            'items' => $plan,
            'classSpecifique' => "getCount"
        ]);
    }

    #[Route('/plan/ajouter', name: 'app_plan_ajouter')]
    public function ajouter(EntityManagerInterface $em, Request $request): Response
    {
        $plan = new Plan();

        $form=$this->createForm(AjoutPlanType::class,$plan);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plan->setDate(new DateTime());
            foreach ($plan->getBatiments() as $batiment) {
                $batiment->setPlan($plan); // Set the `plan` for each Batiment
            }
            $em->persist($plan);
            $em->flush();

            return $this->redirectToRoute('app_plan_liste'); // Redirection après soumission
        }

        return $this->render('plan/ajouter.html.twig', [
            'form' => $form->createView(),
            'css' => 'plan',
            'classItem' => "plan",
            'routeItem'=> "app_plan_ajouter",
            'classSpecifique' => ""
        ]);
    }

    #[Route('/plan/supprimer', name: 'app_plan_supprimer')]
    public function supprimer(EntityManagerInterface $em,
                              Request $request,
                              SessionInterface $session,
                              PlanRepository $planRepo
    ): Response
    {
        $ids = $request->request->all('selected_plans');
        if(empty($ids)) {
            $ids = $session->get('selected_plans', []);
        }
        else
            $session->set('selected_plans', $ids);

        $plans = array_map(fn($id) => $planRepo->find($id), $ids);
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER' // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString=='CONFIRMER'){
                foreach ($plans as $plan ) {
                    $em->remove($plan);
                }
                $em->flush();
                return $this->redirectToRoute('app_plan_liste');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('plan/supprimer.html.twig', [
            'form' => $form->createView(),
            'plans' => $plans,
        ]);
    }

    #[Route('/plan/{id}', name: 'app_plan_infos')]
    public function infos(int $id, PlanRepository $repository, Request $request): Response
    {
        $plan = $repository->find($id);

        return $this->render('plan/infos.html.twig', ['plan'=>$plan]);
    }

    #[Route('/plan/supprimer-selection', name: 'app_plan_supprimer_selection', methods: ['POST', 'GET'])]
    public function suppSelection(
        Request $request,
        PlanRepository $planRepository,
        BatimentRepository $batimentRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        // Fetch the 'selected_batiments' from the request
        $ids = $request->request->all('selected-');

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

        return $this->render('batiment/supprimer_multiple.html.twig', [
            'form' => $form->createView(),
            'items' => $batiments,
            'classItem'=> "batiment"
        ]);
    }
    #[Route('/plan/modifier', name: 'app_plan_modifier')]
    public function modifier(int $id, SARepository $SARepository, EntityManagerInterface $entityManager,Request $request): Response
    {

        // Affichage du formulaire
        return $this->render('accueil/index.html.twig', []);
    }
}
