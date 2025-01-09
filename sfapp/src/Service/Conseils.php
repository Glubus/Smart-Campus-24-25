<?php

namespace App\Service;
use App\Service\ApiWrapper;

class Conseils
{
    public const seuilDepasse = false;
    public const seuilTemp = ['min' => 18, 'max' => 26];
    public const seuilCo2 = ['min' => 250, 'attention' => 800, 'max' => 1200];
    public const seuilHumi = ['min' => 40, 'max' => 60];

    public function getConseils(ApiWrapper $wrapper, float $tempInt, float $co2Int, float $humiInt){
        $tempExt = $wrapper->getTempOutsideByAPI();

        $colTemp = null;
        $colCo2 = null;
        $colHumi = null;

        $imgTemp = null;
        $imgCo2 = null;
        $imgHumi = null;

        $conseilsImportants = null;
        $conseilTemp = null;
        $conseilCo2 = null;
        $conseilHumi = null;

        // Couleur de la police

        switch ($tempInt) {
            case $tempInt < self::seuilTemp['min'] : $colTemp = "#009BCF"; break;
            case $tempInt > self::seuilTemp['max'] : $colTemp = "#DA0000"; break;}
        switch ($co2Int) {
            case $co2Int > self::seuilCo2['max'] : $colCo2 = "#DA0000"; break;}
        switch ($humiInt) {
            case $humiInt < self::seuilHumi['min'] : $colHumi = "#009BCF"; break;
            case $humiInt > self::seuilHumi['max'] : $colHumi = "#00940A"; break;}

        // Conseils sur la température

        if($tempInt < $tempExt-2){
            $conseilTemp .= "Augmentez le chauffage pour équilibrer la température intérieure et extérieure. ";
            $conseilsImportants .= "Augmentez le chauffage pour équilibrer la température intérieure et extérieure. ";
            $imgTemp = "/img/conseils/augmenterChauffage.png";
        } elseif($tempInt > $tempExt+5){
            $conseilTemp .= "Diminuez le chauffage ou ouvrez légèrement les fenêtres pour réduire la température intérieure. ";
            $conseilsImportants .= "Diminuez le chauffage ou ouvrez légèrement les fenêtres pour réduire la température intérieure. ";
            $imgTemp = "/img/conseils/baisserChauffage.png";
        } elseif($tempExt-2 <= $tempInt && $tempInt <= $tempExt+5){
            $conseilTemp .= "La température est optimale. Maintenez les réglages actuels. ";
            $imgTemp = "/img/conseils/satisfaisant.png";
        } if($tempExt < self::seuilTemp['min']){
            $conseilsImportants .= "Limitez les ouvertures de fenêtres pour préserver la chaleur intérieure. ";
        }

        // Conseils sur le niveau de CO2

        if($co2Int < self::seuilCo2['attention']){
            $conseilCo2 .= "Le niveau de CO2 est satisfaisant. Aucune action n'est nécessaire. ";
            $imgCo2 = "/img/conseils/satisfaisant.png";
        } elseif(self::seuilCo2['attention'] <= $co2Int && $co2Int <= self::seuilCo2['max']){
            $conseilCo2 .= "Le niveau de CO2 commence à augmenter. Ouvrez les fenêtres pour ventiler. ";
            $conseilsImportants .= "Le niveau de CO2 commence à augmenter. Ouvrez les fenêtres pour ventiler. ";
            $imgCo2 = "/img/conseils/ouvrirFenetre.png";
        } elseif($co2Int > self::seuilCo2['max']){
            $conseilCo2 .= "Le CO2 est très élevé. Ventilez immédiatement ou activez un système de purification d’air. ";
            $conseilsImportants .= "Le CO2 est très élevé. Ventilez immédiatement ou activez un système de purification d’air. ";
            $imgCo2 = "/img/conseils/ventiler.png";
        }

        // Conseils sur l'humidité

        if($humiInt < self::seuilHumi['min']){
            $conseilHumi .= "L’air est trop sec. Activez un humidificateur ou placez des plantes d’intérieur. ";
            $conseilsImportants .= "L’air est trop sec. Activez un humidificateur ou placez des plantes d’intérieur. ";
            $imgHumi = "/img/conseils/plantes.png";
        } elseif($humiInt > self::seuilHumi['max']){
            $conseilHumi .= "L’air est trop humide. Activez un déshumidificateur ou augmentez la ventilation. ";
            $conseilsImportants .= "L’air est trop humide. Activez un déshumidificateur ou augmentez la ventilation. ";
            $imgHumi = "/img/conseils/ventiler.png";
        } elseif(self::seuilHumi['min'] < $humiInt && $humiInt < self::seuilHumi['max']){
            $conseilHumi .= "Le taux d’humidité est idéal. Aucune action n’est nécessaire. ";
            $imgHumi = "/img/conseils/satisfaisant.png";
        }

        // Recommandations générales

        if(self::seuilTemp['min'] < $tempInt && $tempInt > self::seuilTemp['max'] && $co2Int > self::seuilCo2['max']){
            $conseilsImportants .= "Privilégiez une ventilation ciblée pour réduire le CO2 sans affecter la température. ";
        } if($tempInt < self::seuilTemp['min'] && $co2Int > self::seuilCo2['max']){
            $conseilsImportants .= "Activez une ventilation modérée, idéalement en utilisant un système de récupération de chaleur. ";
        } if($tempInt > self::seuilTemp['max'] && $humiInt > self::seuilHumi['max']){
            $conseilsImportants .= "Utilisez un déshumidificateur et ventilez pour réduire à la fois la chaleur et l’humidité. ";
        } if($tempInt < self::seuilTemp['min'] && $humiInt < self::seuilHumi['min']){
            $conseilsImportants .= "Augmentez légèrement le chauffage et ajoutez un humidificateur. ";
        } if($co2Int > self::seuilCo2['max'] && $humiInt > self::seuilHumi['max']){
            $conseilsImportants .= "Activez un purificateur d’air et un déshumidificateur pour rétablir des conditions saines. ";
        } if($co2Int > self::seuilCo2['max'] && $humiInt < self::seuilHumi['min']){
            $conseilsImportants .= "Ventilez et placez des humidificateurs pour équilibrer l’air intérieur. ";
        }

        return [
            'general' => $conseilsImportants,
            'temp' => ['texte' => $conseilTemp, 'img' => $imgTemp, 'color' => $colTemp],
            'co2' => ['texte' => $conseilCo2, 'img' => $imgCo2, 'color' => $colCo2],
            'humi' => ['texte' => $conseilHumi, 'img' => $imgHumi, 'color' => $colHumi],
        ];
    }
}