<?php

namespace App\Controller;

use App\Entity\DetailIntervention;
use App\Entity\EtatIntervention;
use App\Form\DetailInterventionType;
use App\Form\RechercheSaType;
use App\Repository\DetailInterventionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class  AdminController extends AbstractController
{
    private const ROLE_TECHNICIEN = 'ROLE_TECHNICIEN';

    private function getTechnicians(UtilisateurRepository $repository, ?string $name): array
    {
        return $name
            ? $repository->findTechniciensByRoleAndNom(self::ROLE_TECHNICIEN, $name)
            : $repository->findByRole(self::ROLE_TECHNICIEN);
    }
}