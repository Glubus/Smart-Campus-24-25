<?php

namespace App\Controller;

use App\Entity\DetailPlan;
use App\Entity\EtatInstallation;
use App\Entity\Plan;
use App\Entity\SA;
use App\Entity\Salle;
use App\Form\AssociationSASalle;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\DetailPlanRepository;
use App\Repository\EtageRepository;
use App\Repository\PlanRepository;
use App\Repository\SalleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isNull;

class DetailPlanController extends AbstractController
{
    #[Route('/lier/{id}/ajout', name: 'app_lier_ajout')]
    public function ajouter(EntityManagerInterface $em, Request $request, int $id): Response
    {
        $sa_id = $request->query->get('sa_id');
        $salle_id = $request->query->get('salle');

        $salle = $em->getRepository(Salle::class)->find($salle_id);
        $plan = $em->getRepository(Plan::class)->find($id);
        $detail_plan = new DetailPlan();
        $detail_plan->setSalle($salle);
        $detail_plan->setPlan($plan);

        if ($sa_id){
            $sa = $em->getRepository(SA::class)->find($sa_id);

            if (!$sa) {
                throw $this->createNotFoundException('Système d\'acquisition non trouvé.');
            }
            /*
            // Utiliser findBy pour récupérer tous les plans associés à ce SA
            $planExist = $em->getRepository(DetailPlan::class)->findOneBy([
                'sa' => $sa  // On filtre les plans par l'objet SA
            ]);

            if (!$plan)
            {
                $plan->setSa($sa);
            }*/

            $detail_plan->setSA($sa);
        }

        $form = $this->createForm(AssociationSASalle::class,  $detail_plan); // Assurez-vous d'avoir un formulaire `PlanType`

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $detail_plan->setDateAjout(new DateTime());
            $detail_plan->setEtatSA(EtatInstallation::INSTALLATION);
            $em->persist( $detail_plan);
            $em->flush();

            return $this->redirectToRoute('app_lier_liste', [
                'id' =>  $id
            ]);// Redirection après soumission
        }

        return $this->render('detail_plan/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/lier/{id}', name: 'app_lier_liste')]
    public function list(PlanRepository $planRepo, Request $request, int $id): Response
    {
        $selected_batiment = $request->query->get('batiment');
        $selected_etage = $request->query->get('etage');

        $plan = $planRepo->findOneBy(['id' => $id]);

        $salles = null;
        if($selected_batiment) {
            $batiment = null;
            foreach ($plan->getBatiments() as $b) {
                if ($b->getId() == $selected_batiment) {
                    $batiment = $b;

                    break; // Exit the loop once found
                }
            }

            if ($selected_etage == null) {
                $salles = array_merge(...array_map(fn($etage) => $etage->getSalles()->toArray(),
                    $batiment->getEtages()->toArray()));
            } else {
                $salles = $batiment->getEtages()[$selected_etage]->getSalles()->toArray();
            }
        }

        foreach ($plan->getBatiments() as $b) {
            $etageNames = [];
            foreach ($b->getEtages() as $etage) {
                $etageNames[] = $etage->getNom();
            }

            $batimentsArray[] = [
                'id' => $b->getId(),
                'nom' => $b->getNom(),
                'nomEtages' => $etageNames,
            ];
        }

        // Afficher la liste des plans dans le template
        return $this->render('detail_plan/liste.html.twig', [
            'salles' => $salles,
            'batiments' => $batimentsArray,
            'selected_batiment' => $selected_batiment,
            'selected_etage' => $selected_etage,
            'plan_select' => $plan
        ]);
    }
    #[Route('/lier/{id}/suppression', name: 'app_lier_suppression')]
    public function supprimer(Request $request, int $id, DetailPlanRepository $repo, EntityManagerInterface $em): Response
    {
        $detail_plan = $repo->find($id);

        if ($request->isMethod('POST')) {
            if ($detail_plan) {
                $em->remove($detail_plan);
                $em->flush();
                return $this->redirectToRoute('app_lier_liste', [
                    'id' => $detail_plan->getPlan()->getId()
                ]);
            }
        }
            return $this->render('detail_plan/supprimer.html.twig', [
                "detail_plan" => $detail_plan,
            ]);
    }
}
