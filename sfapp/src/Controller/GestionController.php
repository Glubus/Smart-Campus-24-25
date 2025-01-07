<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\Salle;
use App\Entity\DetailPlan;
use App\Entity\SA;
use App\Entity\Utilisateur;
use App\Form\ajoutBatimentType;
use App\Form\ajoutSalleType;
use App\Form\AssociationSASalle;
use App\Form\ajoutSAType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\SalleRepository;
use App\Repository\UtilisateurRepository;
use App\Service\ApiWrapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
class GestionController extends AbstractController
{
    #[Route('/gestion', name: 'app_gestion')]
    public function gestion(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer les entités
        $batiment = new Batiment();
        $salle = new Salle();
        $plan = new DetailPlan();
        $sa = new SA();

        // Créer les formulaires à partir des types existants
        $batimentForm = $this->createForm(ajoutBatimentType::class, $batiment);
        $salleForm = $this->createForm(ajoutSalleType::class, $salle);
        $planForm = $this->createForm(AssociationSAsalle::class, $plan);
        $saForm = $this->createForm(ajoutSAType::class, $sa);

        // Gérer la soumission de chaque formulaire
        $batimentForm->handleRequest($request);
        $salleForm->handleRequest($request);
        $planForm->handleRequest($request);
        $saForm->handleRequest($request);

        // Vérifier chaque formulaire et persister si nécessaire
        if ($batimentForm->isSubmitted() && $batimentForm->isValid()) {
            $entityManager->persist($batiment);
            $entityManager->flush();
            $this->addFlash('success', 'Bâtiment ajouté avec succès !');
        }

        if ($salleForm->isSubmitted() && $salleForm->isValid()) {
            $entityManager->persist($salle);
            $entityManager->flush();
            $this->addFlash('success', 'Salle ajoutée avec succès !');
        }

        if ($planForm->isSubmitted() && $planForm->isValid()) {
            $entityManager->persist($plan);
            $entityManager->flush();
            $this->addFlash('success', 'DetailPlan ajouté avec succès !');
        }

        if ($saForm->isSubmitted() && $saForm->isValid()) {
            $entityManager->persist($sa);
            $entityManager->flush();
            $this->addFlash('success', 'Système d\'acquisition ajouté avec succès !');
        }

        // Rendu du template avec tous les formulaires
        return $this->render('gestion/index.html.twig', [
            'batimentForm' => $batimentForm->createView(),
            'salleForm' => $salleForm->createView(),
            'planForm' => $planForm->createView(),
            'saForm' => $saForm->createView(),
        ]);
    }
    #[Route('/admin/technicien', name: 'app_technicien_liste')]
    public function gestion_technicien(Request $request, UtilisateurRepository $entityManager): Response
    {
        $items = $entityManager->findByRole('ROLE_USER');

        return $this->render('gestion/liste.html.twig', [
            'css' => 'technicien',
            'classItem' => "technicien",
            'items' => $items,
            'routeItem'=> "app_batiment_ajouter",
            'classSpecifique' => ""
        ]);
    }
    #[Route('/admin/technicien/ajouter', name: 'app_technicien_ajouter')]
    public function ajouter_technicien(): Response
    {

        return $this->redirectToRoute("app_register");
    }
    #[Route('/admin/technicien/{id}', name: 'app_technicien_infos',requirements: ['id' => '\d+'])]
    public function infos(int $id, UtilisateurRepository $repository): Response
    {
        $user=$repository->find($id);
        $interventions = $user->getDetailInterventions();
        return $this->render('gestion/infos.html.twig', [
            'css' => 'technicien',
            'classItem' => "technicien",
            'item' => $user,
            'routeItem'=> "app_technicien_ajouter",
            'interventions' => $interventions,
            'classSpecifique' => ""
        ]);
    }

    #[Route('/admin/technicien/supprimer', name: 'app_technicien_supprimer_selection')]
    public function supprimer(        Request $request,
                                      UtilisateurRepository $repository,
                                      EntityManagerInterface $entityManager,
                                      SessionInterface $session): Response
    {
        $ids = $request->request->all('selected');

        if (empty($ids)) {
            $ids = $session->get('selected', []);
        } else {
            $session->set('selected', $ids);
        }

        $technicien = array_map(fn($id) => $repository->find($id), $ids);


        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $submittedString = $form->get('inputString')->getData();

            if ($submittedString === 'CONFIRMER') {

                if (!is_iterable($technicien)) {
                    throw new \Exception("No buildings found.");
                }
                foreach ($technicien as $tech) {


                    $entityManager->remove($tech);
                }
                $entityManager->flush();
                return $this->redirectToRoute('app_batiment_liste');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrecte.');
            }
        }

        return $this->render('template/supprimer_multiple.html.twig', [
            'form' => $form->createView(),
            'items' => $technicien,
            'classItem'=> "technicien"
        ]);

    }

    #[Route('/admin/dashboard', name: 'app_dashboard')]
    #[Route('/admin/dashboard/{period}', name: 'app_dashboard_period', requirements: ['period' => '\d+'])]
    public function dashboard(ApiWrapper $wrapper, CacheInterface $cache, int $period = 7): Response
    {
        // Générer une clé de cache unique basée sur les paramètres
        $cacheKey = 'dashboard_data_' . $period;

        // Vérifier le cache ou calculer les données si elles ne sont pas présentes
        $cacheDuration = 3600; // Temps de cache en secondes (ici 1 heure)
        $cacheDuration = match ($period) {
            1 => 3600,         // 1 heure pour "1 jour"
            7 => 21600,        // 6 heures pour "7 jours"
            30 => 86400,       // 1 jour pour "30 jours"
            default => 3600,   // Valeur par défaut (1 heure)
        };
        $cachedData = $cache->get($cacheKey, function (ItemInterface $item) use ($wrapper, $period, $cacheDuration) {
            // Définir la durée de vie du cache
            $item->expiresAfter($cacheDuration);

            // Logique principale, si les données ne sont pas dans le cache
            $dateIntervalEnd = new \DateTime('now'); // Date de fin = Maintenant
            $dateIntervalEnd->modify('+1 day');
            $d = 'P'.(string)$period.'D';
            $dateIntervalStart = (clone $dateIntervalEnd)->sub(new \DateInterval($d)); // Soustraire $period jours
            $data = ($wrapper->requestAllSalleByInterval($dateIntervalStart, $dateIntervalEnd));

            // Extraire les données : température, humidité et gaz
            $tempData = [];
            $humidityData = [];
            $gasData = [];

            foreach ($data as $day => $values) {
                // Convertir et ajouter les données si disponibles
                $tempData[] = isset($values['temp']) ? (float) $values['temp'] : null;
                $humidityData[] = isset($values['hum']) ? (float) $values['hum'] : null;
                $gasData[] = isset($values['co2']) ? (float) $values['co2'] : null;
            }

            // Filtrer et calculer des données statistiques
            $filteredTempData = array_filter($tempData, fn($temp) => !is_null($temp));
            $filteredHumidityData = array_filter($humidityData, fn($humidity) => !is_null($humidity));
            $filteredGasData = array_filter($gasData, fn($gas) => !is_null($gas));

            $fixedTempMean = $this->calculateMean($filteredTempData);

            $fixedHumidityMean = $this->calculateMean($filteredHumidityData);

            $fixedGasMean = $this->calculateMean($filteredGasData);
            $tempDeviation = $this->calculateStandardDeviationToTarget($filteredTempData, 21);
            $humidityDeviation = $this->calculateStandardDeviationToTarget($filteredHumidityData, 70);
            $gasDeviation = $this->calculateStandardDeviationToTarget($filteredGasData, 400);
            // Calculer les moyennes selon le period
            if ($period == 1) {
                $data = $wrapper->calculateAverageByDateFor1D($data);
            } elseif ($period == 7) {
                $data = $wrapper->calculateAverageByDateFor7D($data);
            } elseif ($period == 30) {
                $data = $wrapper->calculateAveragesByDateFor30D($data);
            }
            $co2Data = [];
            $tempData = [];
            $humData = [];

            foreach ($data as $date => $values) {
                $co2Data[$date] = $values['co2'] ?? 0;
                $tempData[$date] = $values['temp'] ?? 0;
                $humData[$date] = $values['hum'] ?? 0;
            }

            $weirdData = $wrapper->detectBizarreStations();
            $tempOutside = $wrapper->getTempOutsideByAPI();
            // Regrouper toutes les données calculées dans un tableau pour le cache
            return [
                'co2_data' => json_encode($co2Data),
                'temp_data' => json_encode($tempData),
                'hum_data' => json_encode($humData),
                'selectedPeriod' => $period,
                'temp' => ['ecarttype' => $tempDeviation, 'mean' => $fixedTempMean],
                'hum' => ['ecarttype' => $humidityDeviation, 'mean' => $fixedHumidityMean],
                'co2' => ['ecarttype' => $gasDeviation, 'mean' => $fixedGasMean],
                'tempOutside' =>$tempOutside,
                'weirdData' => $weirdData
            ];
        });

        // Rendre les données mises en cache dans la vue
        return $this->render('gestion/dashboard.html.twig', $cachedData);
    }

    private function calculateMean(array $data): float
    {
        return round(array_sum($data) / count($data),2);
    }
    private function getChartData(int $period, SalleRepository $salle): array
    {

        return $salle->transform($salle->requestSalle("D303")->getContent());
    }
    private function calculateStandardDeviationToTarget(array $data, float $target): float
    {
        // Calcul de l'écart-type à partir du seuil cible
        $variance = array_reduce($data, fn($carry, $value) => $carry + pow($value - $target, 2), 0) / count($data);
        return round(sqrt($variance), 2);
    }
    private function calculateStandardDeviation(array $data, float $mean): float
    {
        // Calcul de l'écart-type à partir des données réelles
        $variance = array_reduce($data, fn($carry, $value) => $carry + pow($value - $mean, 2), 0) / count($data);
        return round(sqrt($variance),2);
    }

    private function generateTemperatureRange(array $data, int $points): array
    {
        // Créer un éventail entre température minimale et maximale observée
        $min = min($data); // Température minimale
        $max = max($data); // Température maximale
        $range = [];

        for ($i = 0; $i < $points; $i++) {
            $range[] = $min + (($max - $min) / ($points - 1)) * $i; // Interpolation linéaire
        }

        return $range;
    }

    private function generateNormalDistribution(array $temperatureRange, float $mean, float $stdDev): array
    {
        // Calcul des valeurs Y (densités) à partir de la loi normale
        return array_map(
            fn($x) => exp(-0.5 * pow(($x - $mean) / $stdDev, 2)) / ($stdDev * sqrt(2 * pi())),
            $temperatureRange
        );
    }
}