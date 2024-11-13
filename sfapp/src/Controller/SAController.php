<?php

namespace App\Controller;

use App\Entity\SA;
use App\Form\AjoutSAType;
use App\Form\SuppressionType;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SAController extends AbstractController
{
    #[Route('/sa/ajout', name: 'app_sa_ajout')]
    public function ajout(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création du nouvel objet
        $SA = new SA();

        // Création du formulaire
        $form = $this->createForm(AjoutSAType::class, $SA);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si le nom est unique
            $existingSA = $entityManager->getRepository(SA::class)->findOneBy(['nom' => $SA->getNom()]);
            if ($existingSA) {
                // Vous pouvez aussi ajouter un message flash pour notifier l'utilisateur
                $this->addFlash('error', 'Le nom saisis est déjà utilisé.');
            }
            else{
                // Sinon, on peut procéder à l'ajout dans la base
                $aDate = new DateTime();
                $SA->setDateAjout($aDate);
                $entityManager->persist($SA);
                $entityManager->flush();

                // Redirection vers la liste des SA
                return $this->redirectToRoute('app_sa_liste');
            }
        }

        // Affichage du formulaire
        return $this->render('sa/ajout.html.twig', [
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
    }
    #[Route('/sa', name: 'app_sa_liste')]
    public function liste(SARepository $saRepo): Response
    {
        $sa = $saRepo->findAll();
        return $this->render('sa/liste.html.twig', [
            "liste_SA" => $sa,
        ]);
    }

    #[Route('/sa/{id}/suppression', name: 'app_sa_suppression')]
    public function suppression(Request $request, int $id, SARepository $repo, EntityManagerInterface $em): Response
    {
        $SA=$repo->find($id);
        if ($SA){
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => $SA->getNom(), // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString==$SA->getNom()){
                $em->remove($SA);
                $em->flush();
                return $this->redirectToRoute('app_sa_liste');
            }
            else {
                $this->addFlash('error', 'La saisis est incorrect.');
            }
        }

        return $this->render('sa/suppression.html.twig', [
            "form" => $form->createView(),
            "SA"=>$SA,
        ]);
        }
        return $this->render('sa/notfound.html.twig', []);
    }
    #[Route('/sa/{id}', name: 'app_sa_infos')]
    public function affichage_SA(Request $request, int $id, SARepository $repo, EntityManagerInterface $em): Response{
            $SA=$repo->find($id);
            return $this->render('sa/info.html.twig', ["SA"=>$SA]);
    }

}
