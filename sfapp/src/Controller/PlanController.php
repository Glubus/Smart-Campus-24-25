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
        $plans=$repo->findAll();
        $index = 0;
        $col1 = []; $col2 = []; $col3 = [];
        foreach ($plans as $plan) {
            if($index%2 == 0){
                $col1[] = $plan;
            } elseif ($index%2 == 1) {
                $col2[] = $plan;
            } else {
                $col3[] = $plan;
            }
            $index++;
        }

        return $this->render('plan/liste.html.twig', [
            'col1' => $col1,
            'col2' => $col2,
            'col3' => $col3,
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
            'classSpecifique' => "",
            'Type'=>'Plan'
        ]);
    }

    #[Route('/plan/supprimer', name: 'app_plan_supprimer')]
    public function supprimer(EntityManagerInterface $em,
                              Request $request,
                              SessionInterface $session,
                              PlanRepository $planRepo
    ): Response
    {
        $ids = $request->request->all('selected');
        if(empty($ids)) {
            $ids = $session->get('selected', []);
        }
        else
            $session->set('selected', $ids);

        $plans = array_map(fn($id) => $planRepo->find($id), $ids);
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER' // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString=='CONFIRMER'){
                foreach ($plans as $plan ) {
                    foreach ($plan->getBatiments() as $batiment) {
                        $batiment->setPlan(null);
                    }
                    $em->remove($plan);
                }
                $em->flush();
                return $this->redirectToRoute('app_plan_liste');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('template/suppression.html.twig', [
            'form' => $form->createView(),
            'items' => $plans,
            'classItem'=> "plan",
        ]);
    }

    #[Route('/plan/{id}', name: 'app_plan_infos', requirements: ['id' => '\d+'])]
    public function infos(int $id, PlanRepository $repository, Request $request): Response
    {
        $plan = $repository->find($id);

        return $this->render('plan/infos.html.twig', ['plan'=>$plan]);
    }


    #[Route('/plan/modifier', name: 'app_plan_modifier')]
    public function modifier(int $id, SARepository $SARepository, EntityManagerInterface $entityManager,Request $request): Response
    {

        // Affichage du formulaire
        return $this->render('accueil/index.html.twig', []);
    }
}
