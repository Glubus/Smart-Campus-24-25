<?php

namespace App\Entity;

enum EtatIntervention:string{
    case EN_COURS = "en cours";
    case TERMINEE = "terminée";
    case EN_ATTENTE = "en attente";
}