<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiWrapper
{
    private CacheInterface $cache; // Injecté via le constructeur ou autowiring
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }
    public const ASSOCIATIONS = [
        "D205" => "ESP-004",
        "D206" => "ESP-008",
        "D207" => "ESP-006",
        "D204" => "ESP-014",
        "D203" => "ESP-012",
        "D303" => "ESP-005",
        "D304" => "ESP-011",
        "C101" => "ESP-007",
        "D109" => "ESP-024",
        "Secrétariat" => "ESP-026",
        "D001" => "ESP-030",
        "D002" => "ESP-028",
        "D004" => "ESP-020",
        "C004" => "ESP-021",
        "C007" => "ESP-022"
    ];

    public const DB = [
        "ESP-004" => "sae34bdk1eq1",
        "ESP-008" => "sae34bdk1eq2",
        "ESP-006" => "sae34bdk1eq3",
        "ESP-014" => "sae34bdk2eq1",
        "ESP-012" => "sae34bdk2eq2",
        "ESP-005" => "sae34bdk2eq3",
        "ESP-011" => "sae34bdl1eq1",
        "ESP-007" => "sae34bdl1eq2",
        "ESP-024" => "sae34bdl1eq3",
        "ESP-026" => "sae34bdl2eq1",
        "ESP-030" => "sae34bdl2eq2",
        "ESP-028" => "sae34bdl2eq3",
        "ESP-020" => "sae34bdm1eq1",
        "ESP-021" => "sae34bdm1eq2",
        "ESP-022" => "sae34bdm1eq3"
    ];



    public function requestSalleByInterval(string $salle, int $page, string $dateStart, string $dateEnd): array
    {
        // Construire une clé unique pour ce cache
        $cacheKey = sprintf('salle_interval_%s_%s_%s_%d', $salle, $dateStart, $dateEnd, $page);

        $externalValue= $this->cache->get($cacheKey, function (ItemInterface $item) use ($salle, $page, $dateStart, $dateEnd) {
            $item->expiresAfter(3600); // 1 heure

            $client = HttpClient::create();
            $headers = [
                'accept' => ' application/ld+json',
                'dbname' => ''.self::DB[self::ASSOCIATIONS[$salle]].'',
                'username' => 'k2eq3',
                'userpass' => 'nojsuk-kegfyh-3cyJmu',
            ];

            $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures/interval?date1='.$dateStart.'&date2='.$dateEnd.'&page='.$page;

            $response = $client->request('GET', $url, [
                'headers' => $headers,
            ]);

            if ($response->getStatusCode() != 200) {
                throw new \RuntimeException(sprintf('Erreur API pour l\'URL : %s', $url));
            }

            return $response->toArray(); // Renvoie les données sous forme d'array
        });
        return $externalValue;
    }



        public function requestSalleByType(string $salle, string $type, int $page = 1, int $limit = 1): array
        {
        if ($limit > 20) {
            $limit = 20;
        }

        // Génération d'une clé de cache unique basée sur les paramètres
        $cacheKey = sprintf('salle_by_type_%s_%s_%d_%d', $salle, $type, $page, $limit);

        $externalValue = $this->cache->get($cacheKey, function (ItemInterface $item) use ($salle, $type, $page, $limit) {
            $item->expiresAfter(3600); // Expiration après 1 heure

            $client = HttpClient::create();
            $headers = [
                'accept' => 'application/ld+json',
                'dbname' => self::DB[self::ASSOCIATIONS[$salle]] ?? '',
                'username' => 'k2eq3',
                'userpass' => 'nojsuk-kegfyh-3cyJmu',
            ];
            $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures/last?nom='.$type.'&limit='.$limit.'&page='.$page;

            $response = $client->request('GET', $url, [
                'headers' => $headers,
            ]);

            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException(sprintf('Erreur API. URL : %s', $url));
            }

            return $response->toArray(); // Renvoie les données sous forme d'array
        });
        return $externalValue;
    }

    public function requestAllSalleLastValue(): array
    {
        $externalValue = $this->cache->get('all_salle_last_value', function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 heure

            $arr = [];
            $types = ["temp", "co2", "hum"]; // Les types de données à récupérer

            foreach (self::ASSOCIATIONS as $salle => $esp) {
                $arr[$salle] = [];

                foreach ($types as $type) {
                    $result = $this->requestSalleByType($salle, $type, 1, 1);

                    $arr[$salle][$type] = empty($result) ? null : reset($result)['valeur'];

                    $date = (empty($result) ? null : (reset($result)['dateCapture']));
                    if (empty($arr[$salle]["dateCapture"]) || (strtotime($arr[$salle]['dateCapture']) < strtotime($date))) {
                        $arr[$salle]["date"] = $date;
                    }
                }
            }

            return $arr;
        });
        return $externalValue;
    }

    public function requestAllSalleByInterval(
        \DateTimeInterface $start,
        \DateTimeInterface $end)
    {
        $i=0;
        $formattedStart = $start->format('Y-m-d'); // YYYY-MM-DD
        $formattedEnd = $end->format('Y-m-d'); //
        $temporaryArr = [];

        foreach (self::ASSOCIATIONS as $salle => $esp) {

            $result = $this->transform($this->requestSalleByInterval($salle, 1, $formattedStart, $formattedEnd));

            if (!empty($result)) {
                $temporaryArr= [...$temporaryArr, ...$result];
            }
        }
        uksort($temporaryArr, fn($a, $b) => strtotime($a) <=> strtotime($b));

        return $temporaryArr;

    }
    public function transform($data):array
    {
        $result = [];
        foreach ($data as $item) {
            $date = $item['dateCapture'];
            $nom = strtolower($item['nom']);
            $valeur = $item['valeur'];
            $result[$date] ??= [];
            $validNames = ['co2', 'temp', 'hum', 'lum', 'pres'];
            if (in_array($nom, $validNames, true)) {
                $result[$date][$nom] = $valeur;
            }
        }

        return $result;
    }

    public function calculateAverageByDateFor1D(array $data): array
    {
        $hourlyData = [];

        // Parcourir chaque entrée et les grouper par heure
        foreach ($data as $datetime => $values) {
            // Extraire la date et l'heure (YYYY-MM-DD HH)
            $hour = substr($datetime, 0, 13);

            if (!isset($hourlyData[$hour])) {
                $hourlyData[$hour] = [
                    'temp' => [],
                    'hum'  => [],
                    'co2'  => [],
                    'pres' => [],
                    'lum'  => [],
                ];
            }

            // Regrouper les valeurs par heure
            foreach ($values as $key => $value) {
                if (!empty($value)) {
                    $hourlyData[$hour][$key][] = $value;
                }
            }
        }

        // Calculer les moyennes pour chaque heure
        $hourlyAverages = [];
        foreach ($hourlyData as $hour => $measurements) {
            $averages = [];
            foreach ($measurements as $key => $values) {
                if (!empty($values)) {
                    // Moyenne uniquement si des valeurs existent
                    $averages[$key] = array_sum($values) / count($values);
                } else {
                    $averages[$key] = null; // Pas de données
                }
            }
            $hourlyAverages[$hour] = $averages;
        }

        return $hourlyAverages;
    }
    public function calculateAverageByDateFor7D(array $data): array
    {
        $twelveHourData = [];

        // Parcourir chaque entrée et les grouper par tranches de 12 heures
        foreach ($data as $datetime => $values) {
            // Extraire la date et déterminer si on est dans la première ou deuxième tranche de 12h
            $date = substr($datetime, 0, 10); // Récupère 'YYYY-MM-DD'
            $hour = (int)substr($datetime, 11, 2); // Récupère l'heure (0-23)

            // Déterminer si c'est 0-11 (premier bloc de 12h) ou 12-23 (second bloc de 12h)
            $period = $hour < 12 ? '00-11' : '12-23';
            $timePeriod = $date . ' ' . $period;

            if (!isset($twelveHourData[$timePeriod])) {
                $twelveHourData[$timePeriod] = [
                    'temp' => [],
                    'hum'  => [],
                    'co2'  => [],
                    'pres' => [],
                    'lum'  => [],
                ];
            }

            // Regrouper les valeurs par tranche de 12 heures
            foreach ($values as $key => $value) {
                if (!empty($value)) {
                    $twelveHourData[$timePeriod][$key][] = $value;
                }
            }
        }

        // Calculer les moyennes pour chaque tranche de 12 heures
        $twelveHourAverages = [];
        foreach ($twelveHourData as $period => $measurements) {
            $averages = [];
            foreach ($measurements as $key => $values) {
                if (!empty($values)) {
                    // Moyenne uniquement si des valeurs existent
                    $averages[$key] = array_sum($values) / count($values);
                } else {
                    $averages[$key] = null; // Pas de données
                }
            }
            $twelveHourAverages[$period] = $averages;
        }

        return $twelveHourAverages;
    }

    public function calculateAveragesByDateFor30D(array $data): array
    {

        $dailyData = [];

        // Parcourir chaque entrée et les grouper par jour
        foreach ($data as $datetime => $values) {
            // Extraire uniquement la date (YYYY-MM-DD)
            $day = substr($datetime, 0, 10);

            if (!isset($dailyData[$day])) {
                $dailyData[$day] = [
                    'temp' => [],
                    'hum' => [],
                    'co2' => [],
                    'pres' => [],
                    'lum' => [],
                ];
            }

            // Regrouper les valeurs par jour
            foreach ($values as $key => $value) {
                if (!empty($value)) {
                    $dailyData[$day][$key][] = $value;
                }
            }
        }

        // Maintenant, calculer les moyennes pour chaque jour
        $dailyAverages = [];
        foreach ($dailyData as $day => $measurements) {
            $averages = [];
            foreach ($measurements as $key => $values) {
                if (!empty($values)) {
                    // Moyenne uniquement si des valeurs existent
                    $averages[$key] = array_sum($values) / count($values);
                } else {
                    $averages[$key] = null; // Pas de données
                }
            }
            $dailyAverages[$day] = $averages;
        }

        return $dailyAverages;
    }


    public function detectBizarreStations(): array
    {
        $bizarreSalles = [];
        $currentTime = new \DateTime();
        $allData = $this->requestAllSalleLastValue(); // Récupère toutes les dernières données des salles

        // Définir les seuils pour CO2 et humidité
        $co2Threshold = 1200; // Seuil en ppm pour le CO2
        $humThreshold = 85;   // Seuil en pourcentage pour l'humidité

        foreach ($allData as $salle => $data) {
            // Initialiser la liste des problèmes pour la salle
            $issues = [];

            // 1. Vérification des données disponibles
            if (empty($data)) {
                $issues[] = 'Aucune donnée disponible.';
            } else {
                // 2. Vérification de la date des données
                if (isset($data['date'])) {
                    try {
                        $lastDate = new \DateTime($data['date']);
                        $dateDifference = $currentTime->getTimestamp() - $lastDate->getTimestamp();

                        if ($dateDifference > 3600) { // Plus d'une heure
                            $issues[] = "Aucun envoi depuis plus d'une heure.";
                        }
                    } catch (\Exception $e) {
                        $issues[] = "Données erronées.";
                    }
                } else {
                    $issues[] = 'Date de la donnée manquante.';
                }

                // 3. Vérification des seuils de température
                if (isset($data['temp']) && !$this->isTemperatureNormal((float)$data['temp'])) {
                    $issues[] = "Température anormale.";
                }

                // 4. Vérification des seuils de CO2
                if (isset($data['co2']) && $data['co2'] > $co2Threshold) {
                    $issues[] = "Concentration de CO2 trop élevée.";
                }

                // 5. Vérification des seuils d'humidité
                if (isset($data['hum']) && $data['hum'] > $humThreshold) {
                    $issues[] = "Humidité trop élevée.";
                }
            }

            // Enregistrer les problèmes détectés pour la salle
            if (!empty($issues)) {
                $bizarreSalles[$salle] = implode(" ", $issues); // Combine les messages en une seule chaîne
            }
        }

        return $bizarreSalles;
    }

    /**
     * Vérifie si une température est normale. TODO: Ajouter la logique avec une API météo.
     */

    public function getTempOutsideByAPI(){
        // Cache la température extérieure toutes les 6 heures
        $externalValue= $this->cache->get('weather_external_temp', function (ItemInterface $item) {
            $item->expiresAfter(21600); // 21600 secondes = 6 heures

            // Appel de l'API OpenWeather
            $client = HttpClient::create();
            $url = sprintf(
                'https://api.openweathermap.org/data/2.5/weather?q=%s&units=%s&appid=%s',
                urlencode("La Rochelle"),
                "metric",
                "3caaaee0f39de46231be3904497ccb56"
            );

            $response = $client->request('GET', $url);

            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException(sprintf('Erreur lors de l\'appel à OpenWeatherAPI : %s', $response->getContent(false)));
            }

            $data = $response->toArray();

            if (!isset($data['main']['temp'])) {
                throw new \RuntimeException('Température extérieure non disponible via l\'API.');
            }

            return $data['main']['temp'];
        });
        return $externalValue;
    }
    public function isTemperatureNormal(float $temperature): bool
    {
        return $temperature > 25 && $this->getTempOutsideByAPI() < $temperature;
    }
}