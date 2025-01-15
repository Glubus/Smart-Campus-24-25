<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\Salle;
use App\Entity\TypeCapteur;
use App\Form\RechercheSalleType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\DetailInterventionRepository;
use App\Repository\DetailPlanRepository;
use App\Repository\EtageRepository;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use App\Service\ApiWrapper;
use App\Service\Conseils;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AjoutSalleType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle_liste')]
    #[Security("is_granted('ROLE_TECHNICIEN') or is_granted('ROLE_CHARGE_DE_MISSION')")]
    public function index(BatimentRepository $batimentRepository, ApiWrapper $wrapper ,Request $request, SalleRepository $salleRepository, DetailInterventionRepository $detailInterventionRepository, DetailPlanRepository $detailPlanRepository): Response
    {
        $currentDateTime = new \DateTime('now');
        $arr = [];
        $form = $this->createForm(RechercheSalleType::class);
        $batiments = $batimentRepository->findAll();
        if (!$batiments){    throw $this->createNotFoundException('Aucun batiment trouvée');}
        foreach ($batiments as $batiment ){
            foreach ($wrapper->requestAllSalleLastValue($batiment) as $salle) {
                $arr = [...$arr, ...$wrapper->transformBySalle($salle)];
            }
        }
        $salles = $salleRepository->findAll();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $salleNom = $form->get('salleNom')->getData();
            if ($salleNom) {
                // Filtrer les salles dont le nom contient la chaîne $salleNom, peu importe où
                $salles = array_filter($salles, function ($salle) use ($salleNom) {
                    return stripos($salle->getNom(), $salleNom) !== false;
                });
                $salles = array_values($salles);
            }
        }

        $col1 = [];
        $col2 = [];
        $col3 = [];

        $index = 0;
        foreach ($salles as $salle) {
            $sa = [];
            foreach ($detailPlanRepository->findBy(['salle' => $salle]) as $detailPlan) {
                $sa[] = $detailPlan->getSA();
            }

            $etat = "Hors-Service";
            $colEtat = "#F30408";
            $data = ['temp' => null, 'date' => null, 'co2' => null, 'hum' => null];
            $lastDataTime = null;
            $dp = null;
            $conseils = null;
            $jours = null;
            $heures = null;
            $minutes = null;

            // Trouve la salle dans le repertory en fonction du nom renvoyé par l'API wrapper
            foreach ($arr as $key => $value) {
                if ($salle->getNom() === $key) {
                    $dp = $detailInterventionRepository->findOneBy(['salle' => $salle]);

                    // Calcule la durée depuis le dernier envoi de données
                    $lastDataTime = new DateTime($value['date']);
                    $interval = $lastDataTime->diff($currentDateTime);
                    $jours = $interval->days; // Total des jours
                    $heures = $interval->h;   // Heures restantes (après division par jours)
                    $minutes = $interval->i; // Minutes restantes (après division par heures)

                    $data = $value;

                    if (isset($data['temp']) && isset($data['co2']) && isset($data['hum'])) {
                        $etat = "Fonctionnelle";
                        $colEtat = "#00D01F";
                    }

                    // Affecte un booléen à isInDanger pour savoir si la salle a un probleme urgent à regler
                    $conseils = new Conseils();
                    $conseils = $conseils->getConseilsParCapteur($wrapper, (float)($data['temp'] ?? null), (float)($data['co2'] ?? null), (float)($data['hum'] ?? null));
                    break;
                }
            }

            if ($dp) {
                $etat = "En intervention";
                $colEtat = "#FF9000";
            }

            if ($index % 3 == 0) {
                $col1[] = [
                    'salle' => $salle,
                    'sa' => $sa,
                    'data' => $data,
                    'etat' => ['texte' => $etat, 'color' => $colEtat],
                    'time' => ['jours' => $jours, 'heures' => $heures, 'minutes' => $minutes],
                    'conseils' => $conseils
                ];
            } elseif ($index % 3 == 1) {
                $col2[] = [
                    'salle' => $salle,
                    'sa' => $sa,
                    'data' => $data,
                    'etat' => ['texte' => $etat, 'color' => $colEtat],
                    'time' => ['jours' => $jours, 'heures' => $heures, 'minutes' => $minutes],
                    'conseils' => $conseils
                ];
            } elseif ($index % 3 == 2) {
                $col3[] = [
                    'salle' => $salle,
                    'sa' => $sa,
                    'data' => $data,
                    'etat' => ['texte' => $etat, 'color' => $colEtat],
                    'time' => ['jours' => $jours, 'heures' => $heures, 'minutes' => $minutes],
                    'conseils' => $conseils
                ];
            }
            $index++;
        }
        return $this->render('salle/liste.html.twig', [
            'col1' => $col1,
            'col2' => $col2,
            'col3' => $col3,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/salle/{id}', name: 'app_salle_infos', requirements: ['id' => '\d+'])]
    public function infos(int $id,  SalleRepository $aRepo, DetailPlanRepository $planRepository): Response
    {
        $salle = $aRepo->find($id);
        $end = new \DateTime();
        $start = (clone $end)->modify('-1 days'); // 7 jours avant
        $arr=[];

        if ($salle->getOnlySa() == -1){
            return $this->render('salle/infos-SansCapteur.html.twig', [
                'salle' => $salle,
            ]);
        }
        // Données des capteurs

        return $this->render('salle/infos.html.twig', [
            'salle' => $salle,
            'data'=>$arr
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/salle/user', name: 'app_salle_user_liste')]
    public function indexUser(ApiWrapper $wrapper, Request $request, SalleRepository $salleRepository, BatimentRepository $batimentRepository, DetailInterventionRepository $detailInterventionRepository): Response
    {
        $currentDateTime = new \DateTime('now');
        $arr=[];
        $form = $this->createForm(RechercheSalleType::class);
        $batiments = $batimentRepository->findAll();
        if (!$batiments){    throw $this->createNotFoundException('Aucun batiment trouvée');}
        foreach ($batiments as $batiment ){
            foreach ($wrapper->requestAllSalleLastValue($batiment) as $salle) {
                $arr = [...$arr, ...$wrapper->transformBySalle($salle)];
            }
        }
        $salles = $salleRepository->findAll();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $salleNom = $form->get('salleNom')->getData();
            if ($salleNom) {
                // Filtrer les salles dont le nom contient la chaîne $salleNom, peu importe où
                $salles = array_filter($salles, function($salle) use ($salleNom) {
                    return stripos($salle->getNom(), $salleNom) !== false;
                });
                $salles = array_values($salles);
            }
        }

        $col1 = [];
        $col2 = [];
        $col3 = [];

        $index = 0;
        foreach ($salles as $salle) {
            $etat = "Hors-Service"; $colEtat = "#F30408";
            $data = ['temp' => null, 'date' => null, 'co2' => null, 'hum' => null];
            $lastDataTime = null;
            $dp = null;
            $conseils = null;
            $jours = null; $heures = null; $minutes = null;
            $isInDanger = false;

            // Trouve la salle dans le repertory en fonction du nom renvoyé par l'API wrapper
            foreach ($arr as $key => $value) {
                if ($salle->getNom() === $key) {
                    $dp = $detailInterventionRepository->findOneBy(['salle' => $salle]);

                    // Calcule la durée depuis le dernier envoi de données
                    $lastDataTime = new DateTime($value['date']);
                    $interval = $lastDataTime->diff($currentDateTime);
                    $jours = $interval->days; // Total des jours
                    $heures = $interval->h;   // Heures restantes (après division par jours)
                    $minutes = $interval->i; // Minutes restantes (après division par heures)

                    $data = $value;

                    if (isset($data['temp']) && isset($data['co2'])&& isset($data['hum'])) {
                        $etat = "Fonctionnelle";
                        $colEtat = "#00D01F";
                    }

                    $conseils = new Conseils();
                    $conseils = $conseils->getConseilsParCapteur($wrapper, (float)($data['temp'] ?? null), (float)($data['co2'] ?? null), (float)($data['hum'] ?? null));
                    break;
                }
            }

            if($dp)
            {
                $etat = "En intervention";
                $colEtat = "#FF9000";
            }

            if($index % 3 == 0){
                $col1[] = [
                    'salle' => $salle,
                    'data' => $data,
                    'etat' => ['texte' => $etat, 'color' => $colEtat],
                    'time' => ['jours' => $jours, 'heures' => $heures, 'minutes' => $minutes],
                    'conseils' => $conseils
                ];
            } elseif($index % 3 == 1){
                $col2[] = [
                    'salle' => $salle,
                    'data' => $data,
                    'etat' => ['texte' => $etat, 'color' => $colEtat],
                    'time' => ['jours' => $jours, 'heures' => $heures, 'minutes' => $minutes],
                    'conseils' => $conseils
                ];
            } elseif($index % 3 == 2){
                $col3[] = [
                    'salle' => $salle,
                    'data' => $data,
                    'etat' => ['texte' => $etat, 'color' => $colEtat],
                    'time' => ['jours' => $jours, 'heures' => $heures, 'minutes' => $minutes],
                    'conseils' => $conseils
                ];
            }
            $index++;
        }

        return $this->render('salle/user_liste.html.twig', [
            'col1' => $col1,
            'col2' => $col2,
            'col3' => $col3,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/salle/user/{id}', name: 'app_salle_user_infos', requirements: ['id' => '\d+'])]
    public function infosUser(ApiWrapper $wrapper, int $id, SalleRepository $salleRepository, DetailPlanRepository $detailPlanRepository)
    {
        $salle = $salleRepository->find($id);
        if ($salle==null){
            throw $this->createNotFoundException('La salle spécifiée n\'existe pas.');
        }
        $plans = $detailPlanRepository->findBy(['salle' => $salle]);
        $moyTemp = null; $moyCo2 = null; $moyHum = null;
        $tempVar = []; $co2Var = []; $humVar = [];
        $tempValue = null; $co2Value = null; $humValue = null;

        $dataSalle = null;
        $conseil = new Conseils();
        $conseilGeneral = new Conseils();

        foreach ($plans as $plan) {

            $tempValue = $wrapper->requestSalleByType($plan->getSA()->getNom(), "temp", 1, 2);
            $co2Value = $wrapper->requestSalleByType($plan->getSA()->getNom(), "co2", 1, 2);
            $humValue = $wrapper->requestSalleByType($plan->getSA()->getNom(), "hum", 1, 2);

            if($tempValue != null && $co2Value != null && $humValue != null){
                switch ($tempValue) {
                    case $tempValue[0]['valeur'] > $tempValue[1]['valeur']: $tempVar = "/img/ArrowUp.png"; break;
                    case $tempValue[0]['valeur'] < $tempValue[1]['valeur']: $tempVar = "/img/ArrowDown.png"; break;
                    case $tempValue[0]['valeur'] == $tempValue[1]['valeur']: $tempVar = "/img/"; break;
                } switch ($co2Value) {
                    case $co2Value[0]['valeur'] > $co2Value[1]['valeur']: $co2Var = "/img/ArrowUp.png"; break;
                    case $co2Value[0]['valeur'] < $co2Value[1]['valeur']: $co2Var = "/img/ArrowDown.png"; break;
                    case $co2Value[0]['valeur'] == $co2Value[1]['valeur']: $co2Var = "/img/"; break;
                } switch ($humValue) {
                    case $humValue[0]['valeur'] > $humValue[1]['valeur']: $humVar = "/img/ArrowUp.png"; break;
                    case $humValue[0]['valeur'] < $humValue[1]['valeur']: $humVar = "/img/ArrowDown.png"; break;
                    case $humValue[0]['valeur'] == $humValue[1]['valeur']: $humVar = "/img/"; break;
                }

                $moyTemp += $tempValue[0]['valeur'];
                $moyCo2 += $co2Value[0]['valeur'];
                $moyHum += $humValue[0]['valeur'];

                $conseil = $conseil->getConseilsParCapteur($wrapper, $tempValue[0]['valeur'], $co2Value[0]['valeur'], $humValue[0]['valeur']);
            
                $dataSalle[] = [
                    'sa' => $plan->getSA(),
                    'conseil' => $conseil,
                    'temp' => ['val' => $tempValue[0]['valeur'], 'variation' => $tempVar],
                    'co2' => ['val' => $co2Value[0]['valeur'], 'variation' => $co2Var],
                    'humi' => ['val' => $humValue[0]['valeur'], 'variation' => $humVar]
                    ];
            }
        }

        if($dataSalle != null){
            $moyTemp = $moyTemp / count($dataSalle);
            $moyCo2 = $moyCo2 / count($dataSalle);
            $moyHum = $moyHum / count($dataSalle);

            $conseilGeneral = $conseilGeneral->getConseilsGeneraux($wrapper, $moyTemp, $moyCo2, $moyHum);
        }

        return $this->render('salle/user_infos.html.twig', [
            'data' => $dataSalle,
            'salle' => $salle,
            'conseilGeneral' => $conseilGeneral,
        ]);
    }


    #[Route('/salle/ajouter', name: 'app_salle_ajouter')]
    public function ajouter(Request $request, SalleRepository $salleRepository, BatimentRepository $batimentRepository, EntityManagerInterface $entityManager): Response
    {
        $salle = new Salle();

        $selection = $request->query->get('batiment');
        $batiments = $batimentRepository->findAll();
        $form = $this->createFormBuilder()
            ->add('Batiment', EntityType::class,[
        'class' => Batiment::class, // Class of the entity
        'choice_label' => 'nom',   // Field to be displayed for each option (the name of the building)
        'label' => 'Bâtiments',  // Label for the field
                'label_attr' => [
                    'class' => 'form-label text-primary',
                    'style' => 'margin-top: 10px;',
                ],
        'placeholder' => 'Selectionner un batiment',
        'attr' => [
            'class' => 'form-control sa-searchable', // Optional: Add custom styles
            'data-live-search' => 'true', // Optional: Add live search
            'style' => 'margin-left: 10px; display: flex; flex-direction: column;',
            'id' => 'batiment_select',
        ]
        ])
            ->add('salle', AjoutSalleType::class, [
                'batiment' => $selection,
                'label' => false,
            ])
        ->getForm();

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $salle = $form->getData()['salle'];
            $salleExistante = $salleRepository->findOneBy(
                ['nom' => $salle->getNom()]);
            if($salleExistante) {
                $this->addFlash('error', 'Cette salle existe déjà');
            }
            else {
                $entityManager->persist($salle);
                $entityManager->flush();
                return $this->redirectToRoute('app_salle_liste');
            }
        }

        if($selection) {
            $form->get('Batiment')->setData($batimentRepository->find($selection));
        }
        return $this->render('salle/ajout.html.twig', [
            'form' => $form->createView(),
            'css' => 'common',
            'classItem' => "salle",
            'routeItem'=> "app_salle_ajouter",
            'classSpecifique' => ""
        ]);
    }

    #[Route('/salle/modifier/{id}', name: 'app_salle_modifier')]
    public function modifier(int $id,Request $request, EntityManagerInterface $entityManager, SalleRepository $salleRepository, Salle $salle): Response
    {
        $salle = $salleRepository->find($id);
        $form = $this->createForm(AjoutSalleType::class, $salle);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $salleExistante = $salleRepository->findBy(['batiment' => $salle->getBatiment(),'etage' => $salle->getEtage(), 'nom' => $salle->getNom()]);
            if($salleExistante) {
                $this->addFlash('error', 'Cette salle existe déjà');
            }
            else {
                $entityManager->persist($salle);
                $entityManager->flush();
                return $this->redirectToRoute('app_salle_liste');
            }
        }

        return $this->render('salle/modification.html.twig', [
            'controller_name' => 'SalleController',
            'form' => $form->createView(),
            'salle' => $salle,
        ]);
    }

    #[Route('/salle/supprimer-selection', name: 'app_salle_supprimer_selection', methods: ['POST', 'GET'])]
    public function supprimerSelection(
        Request $request,
        SalleRepository $salleRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response
    {
        $ids = $request->request->all('selected');
        if(empty($ids)) {
            $ids = $session->get('selected', []);
        }
        else
            $session->set('selected', $ids);

        $salles = array_map(fn($id) => $salleRepository->find($id), $ids);
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER' // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString=='CONFIRMER'){
                foreach ($salles as $salle ) {
                    foreach ($salle->getDetailPlans() as $dp) {
                        $entityManager->remove($dp);
                    }
                    $entityManager->remove($salle);
                }
                $entityManager->flush();
                return $this->redirectToRoute('app_salle_liste');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('template/suppression.html.twig', [
            'form' => $form->createView(),
            'salles' => $salles,
            'css' => 'common',
            'classItem' => "salle",
            'items' => $salles,
        ]);
    }
    #[Route('/salle/saAttribues/{id}', name: 'app_salle_sa')]
    public function saAttribues(int $id, Salle $salle, SARepository $SARepository, DetailPlanRepository $detailPlanRepository, SalleRepository $salleRepository): Response
    {
        $salle = $salleRepository->find($id);
        $SAs = $detailPlanRepository->findBy(['salle' => $id]);

        return $this->render('salle/saAttribues.html.twig', [
            'controller_name' => 'SalleController',
            'salle' => $salle,
            'SAs' => $SAs,
        ]);
    }
}

?>