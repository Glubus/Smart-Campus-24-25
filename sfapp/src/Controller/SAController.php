<?php

namespace App\Controller;

use App\Entity\SA;
use App\Form\AjoutSAType;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SAController extends AbstractController
{
    #[Route('/sa/ajout', name: 'app_sa_ajout')]
    public function index(Request $request, SalleRepository $salleRepository, EntityManagerInterface $entityManager): Response
    {
        $SA = new SA();
        $form = $this->createForm(AjoutSAType::class, $SA);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($SA);
            $entityManager->flush();
        }
        return $this->render('sa/index.html.twig', [
        ]);
    }
}
