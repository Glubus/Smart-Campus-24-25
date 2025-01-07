<?php

namespace App\Service;
use App\Service\ApiWrapper;

class Conseils
{
    public const seuilDepasse = false;
    public const seuil = 21;

    public function getConseils(ApiWrapper $wrapper, float $tempInt, float $co2Int, float $humiInt){
        $tempExt = $wrapper->getTempOutsideByAPI();

        $tempIdeale = false;

        $conseilsImportants = null;
        $conseilTemp = null;
        $conseilCo2 = null;
        $conseilHumi = null;

        if($tempInt < $tempExt-2){
            $conseilTemp .= "Augmentez le chauffage pour équilibrer la température intérieure et extérieure.";
            $conseilsImportants .= "Augmentez le chauffage pour équilibrer la température intérieure et extérieure.";
        } elseif($tempInt > $tempExt+5){
            $conseilTemp .= "Diminuez le chauffage ou ouvrez légèrement les fenêtres pour réduire la température intérieure.";
            $conseilsImportants .= "Diminuez le chauffage ou ouvrez légèrement les fenêtres pour réduire la température intérieure.";
        } elseif($tempExt-2 <= $tempInt && $tempInt <= $tempExt+5){
            $conseilTemp .= "La température est optimale. Maintenez les réglages actuels.";
            $tempIdeale = true;
        } if($tempExt < 0){
            $conseilsImportants .= "Limitez les ouvertures de fenêtres pour préserver la chaleur intérieure.";
        }

        if($co2Int < 800){
            $conseilCo2 .= "Le niveau de CO2 est satisfaisant. Aucune action n'est nécessaire.";
        } elseif(800 <= $co2Int && $co2Int <= 1200){
            $conseilCo2 .= "Le niveau de CO2 commence à augmenter. Ouvrez les fenêtres pour ventiler.";
            $conseilsImportants .= "Le niveau de CO2 commence à augmenter. Ouvrez les fenêtres pour ventiler.";
        } elseif($co2Int > 1200){
            $conseilCo2 .= "Le CO2 est très élevé. Ventilez immédiatement ou activez un système de purification d’air.";
            $conseilsImportants .= "Le CO2 est très élevé. Ventilez immédiatement ou activez un système de purification d’air.";
        }

        if($humiInt < 40){
            $conseilHumi .= "L’air est trop sec. Activez un humidificateur ou placez des plantes d’intérieur.";
            $conseilsImportants .= "L’air est trop sec. Activez un humidificateur ou placez des plantes d’intérieur.";
        } elseif($humiInt > 60){
            $conseilHumi .= "L’air est trop humide. Activez un déshumidificateur ou augmentez la ventilation.";
            $conseilsImportants .= "L’air est trop sec. Activez un humidificateur ou placez des plantes d’intérieur.";
        } elseif(40 < $humiInt && $humiInt < 60){
            $conseilHumi .= "Le taux d’humidité est idéal. Aucune action n’est nécessaire.";
        }

        if($tempIdeale && $co2Int > 1200){
            $conseilsImportants .= "Privilégiez une ventilation ciblée pour réduire le CO2 sans affecter la température.";
        } if($tempInt < 18 && $co2Int > 1200){
            $conseilsImportants .= "Activez une ventilation modérée, idéalement en utilisant un système de récupération de chaleur.";
        } if($tempInt > 26 && $humiInt > 60){
            $conseilsImportants .= "Utilisez un déshumidificateur et ventilez pour réduire à la fois la chaleur et l’humidité.";
        } if($tempInt < 18 && $humiInt < 40){
            $conseilsImportants .= "Augmentez légèrement le chauffage et ajoutez un humidificateur.";
        } if($co2Int > 1200 && $humiInt > 60){
            $conseilsImportants .= "Activez un purificateur d’air et un déshumidificateur pour rétablir des conditions saines.";
        } if($co2Int > 1200 && $humiInt < 40){
            $conseilsImportants .= "Ventilez et placez des humidificateurs pour équilibrer l’air intérieur.";
        }

        return ['general' => $conseilsImportants, 'temp' => $conseilTemp, 'co2' => $conseilCo2, 'humi' => $conseilHumi];
    }
}