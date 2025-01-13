<?php

namespace App\Controller;

use App\Entity\DetailPlan;
use App\Entity\EtatInstallation;
use App\Entity\Plan;
use App\Entity\SA;
use App\Entity\Salle;
use App\Form\AssociationSASalle;
use App\Repository\DetailPlanRepository;
use App\Repository\PlanRepository;
use App\Repository\SalleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DetailPlanController extends AbstractController
{
    #[Route('/plan/{nom}/attribuer', name: 'app_lier_ajout')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function ajouter(EntityManagerInterface $em, Request $request, string $nom): Response
    {
        $sa_id = $request->query->get('sa_id');
        $salle_id = $request->query->get('salle');

        $salle = $em->getRepository(Salle::class)->find($salle_id);
        $plan = $em->getRepository(Plan::class)->findOneBy(['nom' => $nom]);

        $detail_plan = new DetailPlan();
        $detail_plan->setSalle($salle);
        $detail_plan->setPlan($plan);

        if ($sa_id){
            $sa = $em->getRepository(SA::class)->find($sa_id);

            if (!$sa) {
                throw $this->createNotFoundException('Système d\'acquisition non trouvé.');
            }

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
                'nom' =>  $nom
            ]);// Redirection après soumission
        }

        return $this->render('detail_plan/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/plan/{nom}/detail', name: 'app_lier_liste')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function list(PlanRepository $planRepo, SalleRepository $salleRepo, Request $request,  string $nom): Response
    {
        $selected_batiment = $request->query->get('batiment');
        $selected_etage = $request->query->get('etage');

        $plan = $planRepo->findOneBy(['nom' => $nom]);

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

        $form = $this->createFormBuilder()
            ->add('nom', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Rechercher par nom de salle',
                    'class' => 'form-control',
                    ],
               ])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $salleFiltrer = $salleRepo->findByNomRessemblant($data['nom']);
            $salles = array_intersect_key(
                $salles,
                array_intersect(
                    array_map(fn($s):string => $s->getNom(), $salles),
                    array_map(fn($s):string => $s->getNom(), $salleFiltrer)
                )
            );
        }


        // Afficher la liste des plans dans le template
        return $this->render('detail_plan/liste.html.twig', [
            'form' => $form->createView(),
            'salles' => $salles,
            'batiments' => $batimentsArray,
            'selected_batiment' => $selected_batiment,
            'selected_etage' => $selected_etage,
            'plan_select' => $plan,
        ]);
    }

    #[Route('/detail_plan/{id}/suppression', name: 'app_lier_suppression')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function supprimer(Request $request, int $id, DetailPlanRepository $repo, EntityManagerInterface $em): Response
    {
        $detail_plan = $repo->find($id);

        if ($request->isMethod('POST')) {
            if ($detail_plan) {
                if($detail_plan->getEtatInstallation() == EtatInstallation::PRET){
                    $detail_plan->setEtatSA(EtatInstallation::DESINSTALLATION);
                    $detail_plan->setDateEnleve(new DateTime());
                    $em->persist($detail_plan);
                }
                else if($detail_plan->getEtatInstallation() == EtatInstallation::DESINSTALLATION){
                    $detail_plan->setEtatSA(EtatInstallation::PRET);
                    $detail_plan->setDateEnleve(null);
                    $em->persist($detail_plan);
                }
                else
                {
                    $em->remove($detail_plan);
                }
                $em->flush();




                return $this->redirectToRoute('app_lier_liste', [
                    'id' => $detail_plan->getPlan()->getId()
                ]);
            }
        }
            return $this->render('detail_plan/supprimer.html.twig', [
                "detail_plan" => $detail_plan,
                "batiment" => $detail_plan->getSalle()->getEtage()->getBatiment(),
            ]);
    }

    #[Route('/detail_plan/{id}/valider', name: 'app_lier_validation')]
    #[IsGranted('ROLE_TECHNICIEN')]
    public function valider(Request $request, int $id, DetailPlanRepository $repo, EntityManagerInterface $em): Response
    {
        $detail_plan = $repo->find($id);

        if ($request->isMethod('POST')) {
            if ($detail_plan) {
                if($detail_plan->getEtatInstallation() == EtatInstallation::INSTALLATION){
                    $detail_plan->setEtatSA(EtatInstallation::PRET);
                    $detail_plan->setDateAjout(new DateTime());
                    $em->persist($detail_plan);
                    $em->flush();
                }
                else if($detail_plan->getEtatInstallation() == EtatInstallation::DESINSTALLATION){
                    $em->remove($detail_plan);
                    $em->flush();
                }

                return $this->redirectToRoute('app_lier_liste', [
                    'id' => $detail_plan->getPlan()->getId()
                ]);
            }
        }
        return $this->render('detail_plan/valider.html.twig', [
            "detail_plan" => $detail_plan,
            "batiment" => $detail_plan->getSalle()->getEtage()->getBatiment(),
        ]);
    }
}
