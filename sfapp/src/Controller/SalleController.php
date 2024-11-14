<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Bool_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AddSalleType;

class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle')]
    public function index(SalleRepository $salleRepository): Response
    {
        $salles = $salleRepository->findAll();
        $noms = array();
        foreach ($salles as $salle) {
            array_push($noms, $salle->getSalleNom());
        }

        return $this->render('salle/ajout.html.twig', [
            'controller_name' => 'SalleController',
            'salles' => $salles,
            'noms' => $noms,
        ]);
    }

    #[Route('/creerSalle', name: 'app_salle_create')]
    public function ajouter(Request $request, SalleRepository $salleRepository, EntityManagerInterface $entityManager): Response
    {
        $salle = new Salle();
        $form = $this->createForm(AddSalleType::class, $salle);

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

        return $this->render('salle/create.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle,
        ]);
    }

    #[Route('/supprSalle', name: 'app_salle_delete')]
    public function retirer(EntityManagerInterface $entityManager, Request $request, SalleRepository $salleRepository): Response
    {
        $salle = $salleRepository->find($request->get('salle'));
        #$form = $this->createForm();

        $nomSalle = $salle->getSalleNom();

        return $this->render('salle/delete.html.twig', [
            'controller_name' => 'SalleController',
            'nom' => $nomSalle,
        ]);
    }
}