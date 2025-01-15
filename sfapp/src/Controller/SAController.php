<?php

namespace App\Controller;

use App\Entity\ActionLog;
use App\Entity\Commentaires;
use App\Entity\SA;
use Symfony\Component\Security\Core\Security;
use App\Entity\DetailPlan;
use App\Entity\SALog;
use App\Entity\TypeCapteur;
use App\Form\AjoutSAType;
use App\Form\RechercheSaType;
use App\Form\SuppressionType;
use App\Repository\CommentairesRepository;
use App\Repository\SARepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\VarDumper\VarDumper;

class SAController extends AbstractController
{
    #[Route('/sa/modifier/{id}', name: 'app_sa_modifier', requirements: ['id' => '\d+'])]
    public function modifier(int $id, SARepository $SARepository, EntityManagerInterface $entityManager,Request $request): Response
    {
        $SA=$SARepository->find($id);
        if (!$SA)
        {
            return new Response('Page Not Found', 404);
        }
        $form = $this->createForm(AjoutSAType::class, $SA);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {

            if (count($SARepository->findBy(["nom" => $SA->getNom()], ["nom" => "ASC"]))>0){
                $this->addFlash('error', 'Le nom saisi est déjà utilisé.');
            }
            else{
                $LogCrea=new SALog();
                $LogCrea->setSA($SA);

                $LogCrea->setDate(new \DateTime());
                $LogCrea->setAction(ActionLog::MODIFIER);
                $entityManager->persist($LogCrea);
                    $entityManager->flush();

                return $this->redirectToRoute('app_sa_liste');
            }
        }
        // Affichage du formulaire
        return $this->render('sa/ajouter.html.twig', [
            'form' => $form->createView(),
            'css' => 'sa',
            'classItem' => "sa",
            'routeItem'=> "app_sa_modifier",
            'classSpecifique' => "",
            'Type' => "Modifier"
        ]);
    }
    #[Route('/sa/ajouter', name: 'app_sa_ajouter')]
    public function ajoutSA(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sa = new SA();

        $form = $this->createForm(AjoutSAType::class, $sa);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($entityManager->getRepository(SA::class)->findBy(["nom" => $sa->getNom()], ["nom" => "ASC"]))>0){
                $this->addFlash('error', 'Le nom saisi est déjà utilisé.');
            }
            else {
                $entityManager->persist($sa);
                $entityManager->flush();

                $this->addFlash('success', 'SA ajouté avec succès.');

                return $this->redirectToRoute('app_sa_liste');
            }
        }

        return $this->render('sa/ajouter.html.twig', [
            'form' => $form->createView(),
            'css' => 'common',
            'classItem' => "sa",
            'routeItem'=> "app_sa_ajouter",
            'classSpecifique' => "",
            'Type' => "Ajouter"
        ]);
    }

    #[Route('/sa', name: 'app_sa_liste')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function lister(SARepository $saRepo, Request $request): Response
    {
        // Create the form for searching
        $form = $this->createForm(RechercheSaType::class);
        $form->handleRequest($request);

        // Check if the form is submitted and valid, then filter results
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Filter the `SA` entities based on the search term
            $sas = $saRepo->findByNomSA($data['nom']);
        } else {
            // If no filtering, get all entities
            $sas = $saRepo->findAll();
        }

        $index = 0;
        $col1 = [];
        $col2 = [];
        $col3 = [];
        foreach ($sas as $sa) {
            if($index%3 == 0){
                $col1[] = $sa;
            } elseif ($index%3 == 1) {
                $col2[] = $sa;
            } else {
                $col3[] = $sa;
            }
            $index++;
        }

        return $this->render('sa/liste.html.twig', [
            'col1' => $col1,
            'col2' => $col2,
            'col3' => $col3,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/sa/{id}/suppression', name: 'app_sa_suppression')]
    public function supprimer(Request $request, int $id, SARepository $repo, EntityManagerInterface $em): Response
    {
        $SA = $repo->find($id);
        if ($SA) {
            $form = $this->createForm(SuppressionType::class, null, [
                'phrase' => $SA->getNom(), // Passer la variable au formulaire
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $submittedString = $form->get('inputString')->getData();
                if ($submittedString == $SA->getNom()) {
                    $em->remove($SA);
                    $em->flush();
                    $this->addFlash('success', 'SA supprimé avec succès.');
                    return $this->redirectToRoute('app_sa_liste');
                } else {
                    $this->addFlash('error', 'La saisie est incorrecte.');
                }
            }

            return $this->render('template/supprimer.html.twig', [
                "form" => $form->createView(),
                "SA" => $SA,
            ]);
        }

        return $this->render('sa/notfound.html.twig', []);
    }






    #[Route('/sa/log/{id}', name: 'app_sa_log')]
    public function affichage_log_sa(Request $request, int $id, SARepository $repo,): Response
    {
        $SA = $repo->find($id);
        if (!$SA) {
            throw $this->createNotFoundException('SA introuvable.');
        }
        $histo=$SA->getSALogs();

        return $this->render('sa/historique.html.twig', [
            "histo" => $histo,
        ]);
    }



    #[Route('/sa/supprimer-selection', name: 'app_sa_supprimer_selection', methods: ['POST', 'GET'])]
    public function suppSelection(
        Request $request,
        SaRepository $saRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response
    {
        $ids = $request->request->all('selected');
        if(empty($ids)) {
            $ids = $session->get('selected', []);
        }
        else
            $session->set('selected', $ids);

        $sa = array_map(fn($id) => $saRepository->find($id), $ids);
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER' // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString=='CONFIRMER'){

                foreach ($sa as $sas) {
                    // Remove related SALog entries
                    foreach ($sas->getSALogs() as $log) {
                        $entityManager->remove($log);
                    }
                    // Remove the SA entity
                    $entityManager->remove($sas);
                }
                $entityManager->flush();

                return $this->redirectToRoute('app_sa_liste');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('template/suppression.html.twig', [
            'form' => $form->createView(),
            'items' => $sa,
            'classItem'=> "sa",
        ]);
    }
}
