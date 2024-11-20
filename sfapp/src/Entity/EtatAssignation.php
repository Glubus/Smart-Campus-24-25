<?php

namespace App\Entity;

enum EtatAssignation: string
{
    case Actif = 'actif';
    case Inactif = 'inactif';
    case Supprime = 'supprime';
}