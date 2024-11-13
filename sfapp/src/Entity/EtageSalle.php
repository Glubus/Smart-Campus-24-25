<?php

namespace App\Entity;

enum EtageSalle:string{
    case REZDECHAUSSEE = "0";
    case PREMIER = "1";
    case DEUXIEME = "2";
    case TROISIEME = "3";
}
