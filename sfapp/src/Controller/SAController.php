<?php

namespace App\Controller;

use App\Entity\ActionLog;
use App\Entity\SA;
use App\Entity\Capteur;
use App\Entity\DetailPlan;
use App\Entity\SALog;
use App\Entity\TypeCapteur;
use App\Form\AjoutSAType;
use App\Form\RechercheSaType;
use App\Form\SuppressionType;
use App\Repository\SARepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SAController extends AbstractController
{
    #[Route('/sa/modifier/{id}', name: 'app_sa_modifier', requirements: ['id' => '\d+'])]
    public function modifier(int $id, SARepository $SARepository, EntityManagerInterface $entityManager,Request $request): Response
    {
        $SA=$SARepository->find($id);
        if ($SA==null) {
            return new Response('Page Not Found', 404);
        }
        $form = $this->createForm(AjoutSAType::class, $SA);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {

            if (count($SARepository->findBy(["nom" => $SA->getNom()], ["nom" => "ASC"]))>1){
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
        return $this->render('sa/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/sa/ajout', name: 'app_sa_ajout')]
    public function ajouter(SARepository $SARepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Création du nouvel objet SA
        $SA = new SA();

        $form = $this->createForm(AjoutSAType::class, $SA);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $SA->setDateAjout(new \DateTime());

            // Vérifier si le nom est unique
            $existingSA = $entityManager->getRepository(SA::class)->findOneBy(['nom' => $SA->getNom()]);
            if ($existingSA) {
                $this->addFlash('error', 'Le nom saisi est déjà utilisé.');
            }
            else {
                // Persister l'entité SA avant d'ajouter les capteurs
                $entityManager->persist($SA);

                // Ajouter un log de création
                $LogCrea = new SALog();
                $LogCrea->setSA($SA);
                $LogCrea->setDate(new \DateTime());
                $LogCrea->setAction(ActionLog::AJOUTER);
                $entityManager->persist($LogCrea);

                // Sauvegarder dans la base de données
                $entityManager->flush();

                $this->addFlash('success', 'SA et ses capteurs associés ont été ajoutés avec succès.');

                // Redirection vers la liste des SA
                return $this->redirectToRoute('app_sa_liste');
            }
        }

        // Affichage du formulaire
        return $this->render('sa/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/sa', name: 'app_sa_liste')]
    public function lister(SARepository $saRepo, Request $request): Response
    {
        // Create the form for searching
        $form = $this->createForm(RechercheSaType::class);
        $form->handleRequest($request);

        // Check if the form is submitted and valid, then filter results
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Filter the `SA` entities based on the search term
            $sa = $saRepo->findByNomSA($data['nom']);
        } else {
            // If no filtering, get all entities
            $sa = $saRepo->findAll();
        }

        // Render the page with the form and filtered results
        return $this->render('sa/liste.html.twig', [
            "liste_SA" => $sa,
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

            return $this->render('sa/suppression.html.twig', [
                "form" => $form->createView(),
                "SA" => $SA,
            ]);
        }

        return $this->render('sa/notfound.html.twig', []);
    }

    #[Route('/sa/{id}', name: 'app_sa_infos')]
    public function affichage_SA(Request $request, int $id, SARepository $repo,EntityManagerInterface $entityManager): Response
    {
        $SA = $repo->find($id);
        if (!$SA) {
            throw $this->createNotFoundException('SA introuvable.');
        }
        $histo=$SA->getSALogs();
        // trouver la salle d'un Sa
        $plan = $entityManager->getRepository(DetailPlan::class)->findOneBy(['sa' => $SA]);
        $salle = $plan ? $plan->getSalle() : null;

        return $this->render('sa/info.html.twig', [
            "SA" => $SA,
            "salle" => $salle,
            "histo" => $histo,
        ]);
    }
}
