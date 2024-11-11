<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Repository\SalleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AddSalleType;

class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle')]
    public function index(): Response
    {


        return $this->render('salle/index.html.twig', [
            'controller_name' => 'SalleController',
        ]);
    }

    #[Route('/creerSalle', name: 'app_salle_create')]
    public function create(Request $request, SalleRepository $salleRepository): Response
    {
        $salle = new Salle();
        $form = $this->createForm(AddSalleType::class, $salle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $salle->setNom($salle->getBatiment(), $salle->getEtage(), $salle->getNumero());
        }

        return $this->render('salle/create.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle,
        ]);
    }
}
