<?php

namespace App\Controller;

use App\Entity\Plan;
use App\Entity\SA;
use App\Form\AssociationSASalle;
use App\Form\SuppressionType;
use App\Repository\PlanRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanController extends AbstractController
{
    #[Route('/plan/ajouter', name: 'app_plan_ajout')]
    public function ajouter(EntityManagerInterface $em, Request $request): Response
    {
        $sa_id = $request->query->get('sa_id');
        if ($sa_id){
            $sa = $em->getRepository(SA::class)->find($sa_id);

            if (!$sa) {
                throw $this->createNotFoundException('Système d\'acquisition non trouvé.');
            }

            // Utiliser findBy pour récupérer tous les plans associés à ce SA
            $plan = $em->getRepository(Plan::class)->findOneBy([
                'sa' => $sa  // On filtre les plans par l'objet SA
            ]);
            if (!$plan)
            {
                $plan = new Plan();
                $plan->setSa($sa);
            }
        }else{
            $plan = new Plan(); // Créez un objet Plan
        }
        $form = $this->createForm(AssociationSASalle::class, $plan); // Assurez-vous d'avoir un formulaire `PlanType`

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plan->setDateAjout(new DateTime());
            $em->persist($plan);
            $em->flush();

            return $this->redirectToRoute('app_plan_liste'); // Redirection après soumission
        }

        return $this->render('plan/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/plan', name: 'app_plan_liste')]
    public function list(PlanRepository $em): Response
    {
        // Récupérer tous les plans
        $plans = $em->findAll();

        // Afficher la liste des plans dans le template
        return $this->render('plan/liste.html.twig', [
            'plans' => $plans,
        ]);
    }
    #[Route('/plan/{id}/suppression', name: 'app_plan_suppression')]
    public function supprimer(Request $request, int $id, PlanRepository $repo, EntityManagerInterface $em): Response
    {
        $plan = $repo->find($id);
        if ($plan) {
            $phrase=$plan->getSalle()->getNom().' vers '.$plan->getSA()->getNom();
            $form = $this->createForm(SuppressionType::class, null, [
                'phrase' => $phrase, // Passer la variable au formulaire
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $submittedString = $form->get('inputString')->getData();
                if ($submittedString == $phrase) {
                    $em->remove($plan);
                    $em->flush();
                    $this->addFlash('success', 'Attribution supprimé avec succès.');
                    return $this->redirectToRoute('app_plan_liste');
                } else {
                    $this->addFlash('error', 'La saisie est incorrecte.');
                }
            }

            return $this->render('plan/suppression.html.twig', [
                "form" => $form->createView(),
                "plan" => $plan,
            ]);
        }

        return $this->render('sa/notfound.html.twig', []);
    }
}
