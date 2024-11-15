<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanController extends AbstractController
{
    #[Route('/plan/ajouter', name: 'app_plan')]
    public function ajouter(Request $request): Response
    {
        $plan = new Plan(); // Créez un objet Plan
        $form = $this->createForm(PlanType::class, $plan); // Assurez-vous d'avoir un formulaire `PlanType`

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vous pouvez ici persister le plan dans la base de données
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($plan);
            // $entityManager->flush();

            return $this->redirectToRoute('plan_ajouter'); // Redirection après soumission
        }

        return $this->render('plan/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
