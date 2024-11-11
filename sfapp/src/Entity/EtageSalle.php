<?php

namespace App\Entity;

enum EtageSalle:string{
    case RESDECHAUSSE = "0";
    case PREMIER = "1";
    case DEUXIEME = "2";
    case TROISIEME = "3";
}
