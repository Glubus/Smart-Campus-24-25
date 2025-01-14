<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\Salle;
use App\Entity\DetailPlan;
use App\Entity\SA;
use App\Entity\Utilisateur;
use App\Form\AjoutBatimentType;
use App\Form\AjoutSalleType;
use App\Form\AssociationSASalle;
use App\Form\AjoutSAType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\DetailInterventionRepository;
use App\Repository\EtageRepository;
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
        $batimentForm = $this->createForm(AjoutBatimentType::class, $batiment);
        $salleForm = $this->createForm(AjoutSalleType::class, $salle);
        $planForm = $this->createForm(AssociationSAsalle::class, $plan);
        $saForm = $this->createForm(AjoutSAType::class, $sa);

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

    #[Route('/admin/technicien/modifier', name: 'app_technicien_modifier')]
    public function modifier(){
        return $this->render('accueil/index.html.twig');
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

        return $this->render('template/supprimer_multiple.html.twig',
            [
            'form' => $form->createView(),
            'items' => $technicien,
            'classItem'=> "technicien"
        ]);

    }

    #[Route('/outils/diagnostic/{batiment}/{salle}', name: 'app_diagnostic_salle')]
    public function diagnosticSalle(
        string $batiment,
        string $salle,
        ApiWrapper $wrapper,
        CacheInterface $cache,
        SalleRepository $salleRepository,
        DetailInterventionRepository $detailInterventionRepository,
        Request $req,
        int $period = 7
    ): Response {
        $period = $req->get('period') ?? 7;

        $salleEntity = $salleRepository->findOneBy(['nom' => $salle]);
        if (!$salleEntity) {
            throw $this->createNotFoundException("La salle spécifiée n'existe pas.");
        }

        $count = $this->formateLastValue($wrapper->requestSalleLastValueByDateAndInterval($salleEntity));

        $dateIntervalEnd = (new \DateTime('now'))->modify('+1 day +1 hour');
        $dateIntervalStart = (clone $dateIntervalEnd)->modify("-" . $period . " day");

        $data = $wrapper->requestSalleByInterval($salleEntity, 1, $dateIntervalStart->format('Y-m-d'), $dateIntervalEnd->format('Y-m-d'));
        $data = $wrapper->transform($data);

        $tempData = [];
        $humidityData = [];
        $gasData = [];

        foreach ($data as $day => $values) {
            $tempData[] = isset($values['temp']) ? (float)$values['temp'] : null;
            $humidityData[] = isset($values['hum']) ? (float)$values['hum'] : null;
            $gasData[] = isset($values['co2']) ? (float)$values['co2'] : null;
        }

        $filteredTempData = array_filter($tempData, fn($temp) => !is_null($temp));
        $filteredHumidityData = array_filter($humidityData, fn($humidity) => !is_null($humidity));
        $filteredGasData = array_filter($gasData, fn($gas) => !is_null($gas));

        $fixedTempMean = $this->calculateMean($filteredTempData);
        $fixedHumidityMean = $this->calculateMean($filteredHumidityData);
        $fixedGasMean = $this->calculateMean($filteredGasData);

        $tempDeviation = $this->calculateStandardDeviationToTarget($filteredTempData, 21);
        $humidityDeviation = $this->calculateStandardDeviationToTarget($filteredHumidityData, 70);
        $gasDeviation = $this->calculateStandardDeviationToTarget($filteredGasData, 400);

        $data = $wrapper->calculateAveragesByPeriod($data, $period . "D");
        $co2Data = [];
        $tempData = [];
        $humData = [];

        foreach ($data as $date => $values) {
            $co2Data[$date] = $values['co2'] ?? 0;
            $tempData[$date] = $values['temp'] ?? 0;
            $humData[$date] = $values['hum'] ?? 0;
        }

        $tempOutside = $wrapper->getTempOutsideByAPI();

        // Récupérer les commentaires associés à la salle
        $detailInterventions = $detailInterventionRepository->findBy(['salle' => $salleEntity], ['dateAjout' => 'DESC']);

        // Regrouper toutes les données calculées dans un tableau pour la vue
        $cachedData = [
            'co2_data' => json_encode($co2Data),
            'temp_data' => json_encode($tempData),
            'hum_data' => json_encode($humData),
            'selectedPeriod' => $period,
            'temp' => [
                'ecarttype' => $tempDeviation,
                'mean' => $fixedTempMean,
                'lastData' => $this->calculateAverage($count["temp"])
            ],
            'hum' => [
                'ecarttype' => $humidityDeviation,
                'mean' => $fixedHumidityMean,
                'lastData' => $this->calculateAverage($count["hum"])
            ],
            'co2' => [
                'ecarttype' => $gasDeviation,
                'mean' => $fixedGasMean,
                'lastData' => $this->calculateAverage($count["co2"])
            ],
            'tempOutside' => $tempOutside,
            'salle' => $salleEntity->getNom(),
            'batiment' => $batiment,
            'detailInterventions' => $detailInterventions, // Ajouter les interventions
        ];

        return $this->render('gestion/diagnostic_salle.html.twig', $cachedData);
    }
    #[Route('/outils/diagnostic/{batiment}', name: 'app_diagnostic_batiment')]
    public function diagnosticBatiment(
        string $batiment,
        ApiWrapper $wrapper,
        BatimentRepository $bat,
        SalleRepository $salleRepository,
        batimentRepository $batimentRepository,
        EtageRepository $etageRepository,
        CacheInterface $cache,
        DetailInterventionRepository $detailInterventionRepository,
        Request $req,
        int $period = 7
    ): Response {
        $period = $req->get('period') ?? 7;

        $batEntity = $batimentRepository->findOneBy(['nom' => $batiment]);
        if (!$batEntity) {
            throw $this->createNotFoundException("Le bâtiment spécifié n'existe pas.");
        }

        // Récupérer les étages du bâtiment
        $etages = $etageRepository->findBy(['batiment' => $batEntity]);

        // Récupérer les salles associées aux étages du bâtiment
        $salles = [];
        foreach ($etages as $etage) {
            $salles = array_merge($salles, $salleRepository->findBy(['etage' => $etage]));
        }

        $count = $this->formateLastValue($wrapper->requestAllSalleLastValueByDateAndInterval($batEntity, $salleRepository));

        $dateIntervalEnd = (new \DateTime('now'))->modify('+1 hour');
        $dateIntervalStart = (clone $dateIntervalEnd)->modify("-{$period} day");

        $data = $wrapper->requestAllSalleByIntervalv2($batEntity, $salleRepository, $dateIntervalStart, $dateIntervalEnd);

        $tempData = [];
        $humidityData = [];
        $gasData = [];
        $data = $wrapper->transform($data);
        foreach ($data as $day => $values) {
            $tempData[] = isset($values['temp']) ? (float)$values['temp'] : null;
            $humidityData[] = isset($values['hum']) ? (float)$values['hum'] : null;
            $gasData[] = isset($values['co2']) ? (float)$values['co2'] : null;
        }

        $filteredTempData = array_filter($tempData, fn($temp) => !is_null($temp));
        $filteredHumidityData = array_filter($humidityData, fn($humidity) => !is_null($humidity));
        $filteredGasData = array_filter($gasData, fn($gas) => !is_null($gas));

        $fixedTempMean = $this->calculateMean($filteredTempData);
        $fixedHumidityMean = $this->calculateMean($filteredHumidityData);
        $fixedGasMean = $this->calculateMean($filteredGasData);

        $tempDeviation = $this->calculateStandardDeviationToTarget($filteredTempData, 21);
        $humidityDeviation = $this->calculateStandardDeviationToTarget($filteredHumidityData, 70);
        $gasDeviation = $this->calculateStandardDeviationToTarget($filteredGasData, 400);

        $data = $wrapper->calculateAveragesByPeriod($data, "{$period}D");
        $co2Data = [];
        $tempData = [];
        $humData = [];
        foreach ($data as $date => $values) {
            $co2Data[$date] = $values['co2'] ?? 0;
            $tempData[$date] = $values['temp'] ?? 0;
            $humData[$date] = $values['hum'] ?? 0;
        }

        $weirdData = $wrapper->detectBizarreStations($batEntity);
        $tempOutside = $wrapper->getTempOutsideByAPI();

        // Récupérer les interventions associées au bâtiment
        $detailInterventions = $detailInterventionRepository->findByBatiment($batEntity);


        $cachedData = [
            'co2_data' => json_encode($co2Data),
            'temp_data' => json_encode($tempData),
            'hum_data' => json_encode($humData),
            'selectedPeriod' => $period,
            'temp' => [
                'ecarttype' => $tempDeviation,
                'mean' => $fixedTempMean,
                'lastData' => $this->calculateAverage($count['temp'])
            ],
            'hum' => [
                'ecarttype' => $humidityDeviation,
                'mean' => $fixedHumidityMean,
                'lastData' => $this->calculateAverage($count['hum'])
            ],
            'co2' => [
                'ecarttype' => $gasDeviation,
                'mean' => $fixedGasMean,
                'lastData' => round($this->calculateAverage($count['co2']))
            ],
            'tempOutside' => $tempOutside,
            'weirdData' => $weirdData,
            'batiment' => $batiment,
            'salles' => $salles,
            'detailInterventions' => $detailInterventions, // Ajouter les interventions
        ];

        return $this->render('gestion/diagnostic_batiment.html.twig', $cachedData);
    }


    // array = [total,count]
    private function calculateAverage(array $data, int $round=1){
        return round(($data[1] ? $data[0]/$data[1] : $data[0]),$round);
    }
    private function calculateMean(array $data): float
    {
        if (!count($data)){return 0;}
        return round(array_sum($data) / count($data),2);
    }
    private function getChartData(int $period, SalleRepository $salle): array
    {

        return $salle->transform($salle->requestSalle("D303")->getContent());
    }
    private function calculateStandardDeviationToTarget(array $data, float $target): float
    {
        // Calcul de l'écart-type à partir du seuil cible
        if (!count($data)){return 0;}
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

    private function formateLastValue(array $requestLast){
        $count=["temp" =>[0,0], "hum"=>[0,0], "co2"=>[0,0]];

        foreach ($requestLast as $key => $value) {
            if ($value["nom"]==="hum"){
                $count["hum"][0]+=(int)$value["valeur"];$count["hum"][1]++;
            }
            elseif ($value["nom"]==="temp"){
                $count["temp"][0]+=(int)$value["valeur"];$count["temp"][1]++;
            }
            elseif ($value["nom"]==="co2"){
                $count["co2"][0]+=(int)$value["valeur"];$count["co2"][1]++;
            }
        }
        return $count;

    }
}