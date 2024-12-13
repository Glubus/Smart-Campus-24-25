<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Form\AjoutPlanType;
use App\Form\SuppressionType;
use App\Repository\PlanRepository;
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
            'controller_name' => 'PlanController',
            'plans' => $plan
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
            $em->persist($plan);
            $em->flush();

            return $this->redirectToRoute('app_plan_liste'); // Redirection après soumission
        }

        return $this->render('plan/ajouter.html.twig', [
            'form' => $form->createView(),
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

        return $this->render('plan/suppression.html.twig', [
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

}
