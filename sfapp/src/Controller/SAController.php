<?php

namespace App\Controller;

use App\Entity\SA;
use App\Form\AjoutSAType;
use App\Repository\SalleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SAController extends AbstractController
{
    #[Route('/sa/ajout', name: 'app_sa_ajout')]
    public function ajout(Request $request, SalleRepository $salleRepository, EntityManagerInterface $entityManager): Response
    {
        # Creation du nouvelle objet
        $SA = new SA();
        # Creation du form
        $form = $this->createForm(AjoutSAType::class, $SA);
        $form->handleRequest($request);

        # Si bouton soumettre appuyer
        if ($form->isSubmitted() && $form->isValid()) {
            # Ajout de la date d'ajout
            $aDate = new DateTime();
            $SA->setDateAjout($aDate);
            # Ajout dans la base
            $entityManager->persist($SA);
            $entityManager->flush();
            # Redirection vers la liste des SA
            return $this->redirectToRoute('app_sa_liste');
        }
        return $this->render('sa/ajout.html.twig', [
            'form' => $form->createView(), // Passer le formulaire à la vue
        ]);
    }
    #[Route('/sa/liste', name: 'app_sa_liste')]
    public function liste(): Response
    {
        return $this->render('sa/liste.html.twig', [
        ]);
    }

}
