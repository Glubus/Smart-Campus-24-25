<?php

namespace App\Controller;

use App\Entity\Salle;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        return $this->render('salle/index.html.twig', [
            'controller_name' => 'SalleController',
            'salles' => $salles,
            'noms' => $noms,
        ]);
    }

    #[Route('/creerSalle', name: 'app_salle_create')]
    public function create(Request $request, SalleRepository $salleRepository, EntityManagerInterface $entityManager): Response
    {
        $salle = new Salle();
        $form = $this->createForm(AddSalleType::class, $salle);

        $form->handleRequest($request);

        $data = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if(ctype_digit($data->getNumero())) {
                $entityManager->persist($salle);
                $entityManager->flush();
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
}