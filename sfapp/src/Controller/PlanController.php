<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Form\AjoutPlanType;
use App\Repository\PlanRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/plan/{id}', name: 'app_plan_infos')]
    public function infos(int $id, PlanRepository $repository, Request $request): Response
    {
        $plan = $repository->find($id);

        return $this->render('plan/infos.html.twig', ['plan'=>$plan]);
    }

}
