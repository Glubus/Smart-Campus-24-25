<?php

namespace App\Entity;

enum TypeCapteur:string{
    case temperature = "temperature";
    case humidite = "humidite";
    case co2 = "co2";
}