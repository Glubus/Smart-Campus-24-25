<?php

namespace App\Entity;

enum TypeCapteur:string{
    case TEMPERATURE = "temperature";
    case HUMIDITE = "humidite";
    case CO2 = "co2";
    case LUMINOSITY = "luminosite";
}