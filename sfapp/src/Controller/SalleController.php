<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Form\ModificationSalleType;
use App\Form\SuppressionType;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Bool_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AjoutSalleType;

class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle')]
    public function index(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findAll();

        if($salles) {
            $noms = array();
            foreach ($salles as $salle) {
                array_push($noms, $salle->getSalleNom());
            }

            return $this->render('salle/index.html.twig', [
                'controller_name' => 'SalleController',
                'salles' => $salles,
                'noms' => $noms,
            ]);
        }
        else {
            return $this->render('salle/notfound.html.twig', []);
        }
    }

    #[Route('/creerSalle', name: 'app_salle_create')]
    public function ajouter(Request $request, SalleRepository $salleRepository, EntityManagerInterface $entityManager): Response
    {
        $salle = new Salle();
        $form = $this->createForm(AjoutSalleType::class, $salle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(ctype_digit($salle->getNumero())) {
                $salleExistante = $salleRepository->findBy(['batiment' => $salle->getBatiment(),'etage' => $salle->getEtage(), 'numero' => $salle->getNumero()]);
                if($salleExistante) {
                    $this->addFlash('error', 'Cette salle existe déjà');
                }
                else {
                    $entityManager->persist($salle);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_salle');
                }
            }
            else {
                $this->addFlash('error', 'Entiers uniquement');
            }
        }

        return $this->render('salle/ajout.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle,
        ]);
    }

    #[Route('/supprSalle', name: 'app_salle_delete')]
    public function retirer(EntityManagerInterface $entityManager, Request $request, SalleRepository $salleRepository): Response
    {
        $salle = $salleRepository->find($request->get('salle'));
        if($salle) {
            $form = $this->createForm(SuppressionType::class, null, [
                'phrase' => $salle->getSalleNom(), // Passer la variable au formulaire
            ]);
            $form->handleRequest($request);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString==$salle->getSalleNom()){
                $entityManager->remove($salle);
                $entityManager->flush();
                return $this->redirectToRoute('app_salle');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('salle/suppression.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle->getSalleNom(),
        ]);
    }

    #[Route('/modifierSalle', name: 'app_salle_update')]
    public function modifier(Request $request, EntityManagerInterface $entityManager, SalleRepository $salleRepository, Salle $salle): Response
    {
        $salle = $salleRepository->find($request->get('salle'));
        $form = $this->createForm(AjoutSalleType::class, $salle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            if(ctype_digit($salle->getNumero())) {
                $salleExistante = $salleRepository->findBy(['batiment' => $salle->getBatiment(),'etage' => $salle->getEtage(), 'numero' => $salle->getNumero()]);
                if($salleExistante) {
                    $this->addFlash('error', 'Cette salle existe déjà');
                }
                else {
                    $entityManager->persist($salle);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_salle');
                }
            }
            else {
                $this->addFlash('error', 'Entiers uniquement');
            }
        }

        return $this->render('salle/modification.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle->getSalleNom(),
        ]);
    }
}