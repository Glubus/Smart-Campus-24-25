<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'app_page_acceuil_bis')]
    #[Route('/', name: 'app_page_acceuil')]
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig');
    }
}

