<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Form\AssociationSASalle;
use App\Repository\PlanRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanController extends AbstractController
{
    #[Route('/plan/ajouter', name: 'app_plan')]
    public function ajouter(EntityManagerInterface $em, Request $request): Response
    {
        $plan = new Plan(); // Créez un objet Plan
        $form = $this->createForm(AssociationSASalle::class, $plan); // Assurez-vous d'avoir un formulaire `PlanType`

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plan->setDateAjout(new DateTime());
            $em->persist($plan);
            $em->flush();

            return $this->redirectToRoute('plans_liste'); // Redirection après soumission
        }

        return $this->render('plan/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/plans', name: 'plans_liste')]
    public function list(PlanRepository $em): Response
    {
        // Récupérer tous les plans
        $plans = $em->findAll();

        // Afficher la liste des plans dans le template
        return $this->render('plan/liste.html.twig', [
            'plans' => $plans,
        ]);
    }
}
