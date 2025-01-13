<?php

namespace App\Service;

use App\Entity\Batiment;
use App\Entity\Salle;
use App\Repository\SalleRepository;
use DateTime;
use DateTimeInterface;
use Exception;
use RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ApiWrapper
{
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
    ]; // Injecté via le constructeur ou autowiring
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
private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function requestSalleLastValueByDateAndInterval(Salle $salle): array
    {
        $end = (new DateTime('now'))->modify('+1 hour');
        $start = (clone $end)->modify('-1 hour');
        $types = ["temp", "co2", "hum"]; // Les types de données à récupérer
        $temporaryArr = [];
        $sas = $this->getSA($salle);
        foreach ($types as $type) {
            foreach ($sas as $sa) {
                $results = $this->requestSalleByType($sa, $type, 1, 1);

                foreach ($results as $result) {
                    // Vérifie que la date est présente et valide
                    if (isset($result['dateCapture'])) {
                        try {
                            $date = new DateTime($result['dateCapture']);

                            if ($date >= $start && $date <= $end) {
                                $temporaryArr[] = ($result); // Ajoute uniquement si elle est valide
                            }
                        } catch (Exception $e) {
                            // Ignorer si la date n'est pas valide
                        }
                    }
                }
            }

        }
        return $temporaryArr;
    }

    public function getSA(Salle $salle): array
    {
        $dp = $salle->getDetailPlans();
        $arr = [];
        foreach ($dp as $key => $value) {
            $arr[] = $value->getSA()->getNom();
        }

        return $arr;
    }

    public function requestSalleByType(string $sa, string $type, int $page = 1, int $limit = 1): array
    {
        if ($limit > 20) {
            $limit = 20;
        }
        // Génération d'une clé de cache unique basée sur les paramètres
        $cacheKey = sprintf('salle_by_type_%s_%s_%d_%d', $sa, $type, $page, $limit);

        $externalValue = $this->cache->get($cacheKey, function (ItemInterface $item) use ($sa, $type, $page, $limit) {
            $item->expiresAfter(3600); // Expiration après 1 heure

            $client = HttpClient::create();
            $headers = [
                'accept' => 'application/ld+json',
                'dbname' => self::DB[$sa] ?? '',
                'username' => 'k2eq3',
                'userpass' => 'nojsuk-kegfyh-3cyJmu',
            ];
            $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures/last?nom=' . $type . '&limit=' . $limit . '&page=' . $page;

            $response = $client->request('GET', $url, [
                'headers' => $headers,
            ]);

            if (200 !== $response->getStatusCode()) {
                var_dump($headers);
                var_dump($sa);
                throw new RuntimeException(sprintf('Erreur API. URL : %s', $url));
            }

            return $response->toArray(); // Renvoie les données sous forme d'array
        });
        return $externalValue;
    }

    public function requestAllSalleLastValueByDateAndInterval(Batiment $batiment, SalleRepository $salleRepository): array
    {
        $externalValue = $this->cache->get('all_salle_last_value_by_date', function (ItemInterface $item) use ($salleRepository, $batiment) {
            $item->expiresAfter(3600); // 1 heure
            $end = (new DateTime('now'))->modify('+1 hour');
            $start = (clone $end)->modify('-1 hour');
            $temporaryArr = [];
            $types = ["temp", "co2", "hum"]; // Les types de données à récupérer
            $allSalle = $batiment->getAllSalle();
            $sas=[];
            foreach ($allSalle as $salle) {
                $sas = [...$sas, ...$this->getSA($salle)];
            }
            foreach ($sas as $sa) {
                    foreach ($types as $type) {
                        // Récupère les résultats pour le type demandé
                        $results = $this->requestSalleByType($sa, $type, 1, 1);
                        foreach ($results as $result) {
                            // Vérifie que la date est présente et valide
                            if (isset($result['dateCapture'])) {
                                try
                                {
                                    $date = new DateTime($result['dateCapture']);

                                    if ($date >= $start && $date <= $end)
                                    {
                                        $temporaryArr[] = ($result); // Ajoute uniquement si elle est valide
                                    }
                                }
                                catch (Exception $e)
                                {

                                }
                            }
                        }
                    }
                }

            return $temporaryArr;
        });

        return $externalValue;
    }

    /**
     * @deprecated Utiliser la méthode requestAllSalleByIntervalV2 à la place.
     */
    public function requestAllSalleByInterval(
        DateTimeInterface $start,
        DateTimeInterface $end): array
    {
        $i = 0;
        $formattedStart = $start->format('Y-m-d'); // YYYY-MM-DD
        $formattedEnd = $end->format('Y-m-d'); //
        $temporaryArr = [];

        foreach (self::ASSOCIATIONS as $salle => $esp) {

            $result = $this->transform($this->requestSalleByInterval($salle, 1, $formattedStart, $formattedEnd));

            if (!empty($result)) {
                $temporaryArr = [...$temporaryArr, ...$result];
            }
        }
        uksort($temporaryArr, fn($a, $b) => strtotime($a) <=> strtotime($b));

        return $temporaryArr;

    }

    public function transform($data): array
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
    public function transformBySalle($data): array
    {
        $result = [];
        foreach ($data as $item) {
            $salle = $item['localisation'];
            $date = $item['dateCapture'];
            $nom = strtolower($item['nom']);
            $valeur = $item['valeur'];
            $result[$salle] ??= [];
            $validNames = ['co2', 'temp', 'hum', 'lum', 'pres'];
            if (in_array($nom, $validNames, true)) {
                $result[$salle][$nom] = $valeur;
                $result[$salle]['date'] = $date;
            }
        }

        return $result;
    }

    public function requestSalleByInterval(Salle $salle, int $page, string $dateStart, string $dateEnd): array
    {
        // Construire une clé unique pour ce cache
        $cacheKey = sprintf('salle_interval_%s_%s_%s_%d', $salle->getNom(), $dateStart, $dateEnd, $page);

        $externalValue = $this->cache->get($cacheKey, function (ItemInterface $item) use ($salle, $page, $dateStart, $dateEnd) {
            $item->expiresAfter(3600); // 1 heure
            $result=[];
            $sas = $this->getSA($salle);
            foreach($sas as $sa ){
            $client = HttpClient::create();

            $headers = [
                'accept' => ' application/ld+json',
                'dbname' => self::DB[$sa],
                'username' => 'k2eq3',
                'userpass' => 'nojsuk-kegfyh-3cyJmu',
            ];

            $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures/interval?date1=' . $dateStart . '&date2=' . $dateEnd . '&page=' . $page;

            $response = $client->request('GET', $url, [
                'headers' => $headers,
            ]);

            if ($response->getStatusCode() != 200) {
                throw new RuntimeException(sprintf('Erreur API pour l\'URL : %s', $url));
            }
            $result= [...$result, ...$response->toArray()];
            }
            return $result; // Renvoie les données sous forme d'array
        });
        return $externalValue;
    }


    public function calculateAveragesByPeriod(array $data, string $period): array
    {
        $groupedData = [];
        // Parcourir chaque entrée et les grouper en fonction de la période choisie
        foreach ($data as $datetime => $values) {
            switch ($period) {
                case '1D': // Grouper par heure
                    $key = substr($datetime, 0, 13); // YYYY-MM-DD HH
                    break;

                case '7D': // Grouper par tranche de 12 heures
                    $date = substr($datetime, 0, 10); // YYYY-MM-DD
                    $hour = (int)substr($datetime, 11, 2); // Heure (0-23)
                    $periodHalf = ($hour < 12) ? '00-11' : '12-23';
                    $key = $date . ' ' . $periodHalf;
                    break;

                case '30D': // Grouper par jour
                    $key = substr($datetime, 0, 10); // YYYY-MM-DD
                    break;

                default:
                    throw new InvalidArgumentException(sprintf("Période invalide : %s. Utilisez '1D', '7D' ou '30D'.", $period));
            }

            // Initialiser la clé si elle n'existe pas encore
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'temp' => [],
                    'hum' => [],
                    'co2' => [],
                    'pres' => [],
                    'lum' => [],
                ];
            }

            // Regrouper les valeurs
            foreach ($values as $keyData => $value) {
                if (!empty($value)) {
                    $groupedData[$key][$keyData][] = $value;
                }
            }
        }

        // Calculer les moyennes pour chaque groupe
        $averagesData = [];
        foreach ($groupedData as $timeGroup => $measurements) {
            $averages = [];
            foreach ($measurements as $keyData => $values) {
                if (!empty($values)) {
                    $averages[$keyData] = array_sum($values) / count($values);
                } else {
                    $averages[$keyData] = null; // Pas de données
                }
            }
            $averagesData[$timeGroup] = $averages;
        }
        return $averagesData;
    }

    public function detectBizarreStations(Batiment $bat): array
    {
        $bizarreSalles = [];
        $currentTime = new DateTime();
        $allData = $this->requestAllSalleLastValue($bat); // Récupère toutes les dernières données des salles
        $co2Threshold = 1200; // Seuil en ppm pour le CO2
        $humThreshold = 85;   // Seuil en pourcentage pour l'humidite
        $arr = [];

        foreach ($allData as $salle => $d) {
            foreach ($d as $data){
                // Initialiser la liste des problèmes pour la salle
                $issues = [];

                // 1. Vérification des données disponibles
                if (empty($data)) {
                    $issues[] = 'Aucune donnée disponible.';
                } else {
                    // 2. Vérification de la date des données
                    if (isset($data['dateCapture'])) {
                        try {
                            $lastDate = new DateTime($data['dateCapture']);
                            $dateDifference = $currentTime->getTimestamp() - $lastDate->getTimestamp();

                            if ($dateDifference > 3600) { // Plus d'une heure
                                $issues[] = "Aucun envoi depuis plus d'une heure.";
                            }
                        } catch (Exception $e) {
                            $issues[] = "Données erronées.";
                        }
                    } else {
                        $issues[] = 'Date de la donnée manquante.';
                    }

                    // 3. Vérification des seuils de température
                    if (isset($data['nom']) && $data['nom']=='temp' && $this->isTemperatureNormal((float)$data['valeur'])) {
                        $issues[] = "Température anormale.";
                    }

                    // 4. Vérification des seuils de CO2
                    if (isset($data['nom']) && $data['nom']=='co2' && !$data['valeur'] > $co2Threshold)
                    {
                        $issues[] = "Concentration de CO2 trop élevée.";
                    }

                    // 5. Vérification des seuils d'humidité
                    if (isset($data['nom']) && $data['nom']=='hum' && !$data['valeur'] > $humThreshold) {
                        $issues[] = "Humidité trop élevée.";
                    }
                }

                // Enregistrer les problèmes détectés pour la salle
                if (!empty($issues)) {
                    $bizarreSalles[$salle] = implode(" ", $issues); // Combine les messages en une seule chaîne
                }
            }
        }
        return $bizarreSalles;
    }

    public function requestAllSalleLastValue(Batiment $batiment): array
    {
        // Clé de cache unique
        $externalValue = $this->cache->get('all_salle_last_value', function (ItemInterface $item) use ($batiment) {
            $item->expiresAfter(3600); // Expiration du cache 1 heure

            $types = ["temp", "co2", "hum"]; // Les types de données à récupérer
            $temporaryArr = []; // Tableau temporaire pour stocker les résultats par salle

            // Récupérer toutes les salles associées au bâtiment via getAllSalle
            $allSalle = $batiment->getAllSalle();
            foreach ($allSalle as $salle) {
                // Récupérer toutes les "SA" associées à la salle
                $sas = $this->getSA($salle);
                foreach ($sas as $sa) {
                    foreach ($types as $type) {

                        // Appel de l'API pour obtenir les données du type demandé
                        $results = $this->requestSalleByType($sa, $type, 1, 1);

                        foreach ($results as $result) {
                            // Vérifie que la date est présente et récupère les résultats valides
                            if (isset($result['dateCapture'])) {
                                try {
                                    // Convertit en objet DateTime pour effectuer des comparaisons
                                    $date = new DateTime($result['dateCapture']);
                                    $name=$salle->getNom();
                                    // Ajoute les résultats dans le tableau temporaire
                                    $result['valeur'] = (int)$result['valeur'];
                                    $temporaryArr[$name][] = $result;
                                } catch (Exception $e) {
                                    // Ignorer les résultats invalides
                                }
                            }
                        }
                    }
                }
            }
            return $temporaryArr;
        });

        return $externalValue;
    }

    public function isTemperatureNormal(float $temperature): bool
    {
        return $temperature > 25 && $this->getTempOutsideByAPI() < $temperature;
    }

    public function getTempOutsideByAPI()
    {
        // Cache la température extérieure toutes les 6 heures
        $externalValue = $this->cache->get('weather_external_temp', function (ItemInterface $item) {
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
                throw new RuntimeException(sprintf('Erreur lors de l\'appel à OpenWeatherAPI : %s', $response->getContent(false)));
            }

            $data = $response->toArray();

            if (!isset($data['main']['temp'])) {
                throw new RuntimeException('Température extérieure non disponible via l\'API.');
            }

            return $data['main']['temp'];
        });
        return $externalValue;
    }
    public function requestAllSalleByIntervalv2(
        Batiment $batiment,
        SalleRepository $salleRepository,
        DateTimeInterface $start,
        DateTimeInterface $end
    ): array {
        $now = new DateTime('now'); // Date actuelle
        $temporaryArr = [];
        $types = ["temp", "co2", "hum"]; // Les types de données à récupérer

        // Si la plage demandée est entièrement dans le passé
        if ($end < $now) {
            $cacheKeyPast = sprintf('salle_interval_past_%s_%s', $start->format('Ymd'), $end->format('Ymd'));

            return $this->cache->get($cacheKeyPast, function (ItemInterface $item) use ($start, $end, $batiment, $salleRepository, $types) {
                $item->expiresAfter(2592000); // 1 mois
                $temporaryResults = [];

                // Récupère toutes les salles associées au bâtiment
                $allSalle = $batiment->getAllSalle();

                foreach ($allSalle as $salle) {
                    $sas = $this->getSA($salle);

                    foreach ($sas as $sa) {
                        foreach ($types as $type) {
                            // Appelle l'API pour la période définie
                            $results = $this->requestSalleByInterval($sa, 1, $start->format('Y-m-d'), $end->format('Y-m-d'));

                            foreach ($results as $result) {
                                // Vérifie que la date est présente et valide
                                if (isset($result['dateCapture'])) {
                                    try {
                                        $date = new DateTime($result['dateCapture']);

                                        // Vérifie que la donnée correspond bien à l'intervalle
                                        if ($date >= $start && $date <= $end) {
                                            $temporaryResults[] = $result;
                                        }
                                    } catch (Exception $e) {
                                        // Ignore les dates erronées
                                    }
                                }
                            }
                        }
                    }
                }

                // Trie les résultats par date
                uksort($temporaryResults, fn($a, $b) => strtotime($a) <=> strtotime($b));
                return $temporaryResults;
            });
        }

        // Si la plage demandée inclut du passé et du futur
        if ($start < $now) {
            $pastEnd = (clone $now)->modify('-1 second'); // Juste avant "now"
            $cacheKeyPast = sprintf('salle_interval_past_%s_%s', $start->format('Ymd'), $pastEnd->format('Ymd'));

            // Récupère les données cachées pour la partie passée
            $pastData = $this->cache->get($cacheKeyPast, function (ItemInterface $item) use ($start, $pastEnd, $batiment, $salleRepository, $types) {
                $item->expiresAfter(2592000); // 1 mois
                $temporaryResults = [];

                $allSalle = $batiment->getAllSalle();

                foreach ($allSalle as $salle) {

                        foreach ($types as $type) {
                            // Récupère toutes les valeurs
                            $results = $this->requestSalleByInterval($salle, 1, $start->format('Y-m-d'), $pastEnd->format('Y-m-d'));

                            foreach ($results as $result) {
                                if (isset($result['dateCapture'])) {
                                    try {
                                        $date = new DateTime($result['dateCapture']);

                                        if ($date >= $start && $date <= $pastEnd) {
                                            $temporaryResults[] = $result;
                                        }
                                    } catch (Exception $e) {
                                        // Ignore les dates illégales
                                    }
                                }
                            }
                        }
                    }


                // Trie les résultats
                uksort($temporaryResults, fn($a, $b) => strtotime($a) <=> strtotime($b));
                return $temporaryResults;
            });

            $temporaryArr = [...$temporaryArr, ...$pastData];
        }

        // Trier les résultats finaux par date
        uksort($temporaryArr, fn($a, $b) => strtotime($a) <=> strtotime($b));

        return $temporaryArr;
    }
    public function getSallesWithIssues(Batiment $batiment): array
    {
        $externalValue = $this->cache->get('salles_with_issues_and_ok', function (ItemInterface $item) use ($batiment) {
            $item->expiresAfter(3600);

            $allSalles = $batiment->getAllSalle();
            $types = ["temp", "co2", "hum"];
            $thresholds = [
                'co2' => 1200,
                'hum' => 85,
                'temp' => [15, 30]
            ];

            $sallesWithIssues = [];
            $sallesWithoutIssues = [];

            foreach ($allSalles as $salle) {
                $sas = $this->getSA($salle);
                $hasIssue = false;

                foreach ($sas as $sa) {
                    foreach ($types as $type) {
                        $results = $this->requestSalleByType($sa, $type, 1, 1);

                        foreach ($results as $result) {
                            if (isset($result['dateCapture']) && isset($result['valeur'])) {
                                $value = (float)$result['valeur'];
                                $dateCapture = new DateTime($result['dateCapture']);

                                if ((new DateTime())->getTimestamp() - $dateCapture->getTimestamp() > 3600 ||
                                    ($type === 'co2' && $value > $thresholds['co2']) ||
                                    ($type === 'hum' && $value > $thresholds['hum']) ||
                                    ($type === 'temp' && ($value < $thresholds['temp'][0] || $value > $thresholds['temp'][1]))
                                ) {
                                    $sallesWithIssues[] = $salle->getNom();
                                    $hasIssue = true;
                                    break 3;
                                }
                            } else {
                                $sallesWithIssues[] = $salle->getNom();
                                $hasIssue = true;
                                break 3;
                            }
                        }
                    }
                }

                if (!$hasIssue) {
                    $sallesWithoutIssues[] = $salle->getNom();
                }
            }

            return [
                'issues' => array_unique($sallesWithIssues),
                'ok' => array_unique($sallesWithoutIssues),
            ];
        });

        return $externalValue;
    }


}