<?php

namespace App\Entity;

enum ActionLog: string
{
    case AJOUTER = "AJOUTER";
    case SUPPRIMER= "SUPPRIMER";
    case MODIFIER = "MODIFIER";

}