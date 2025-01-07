<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\Salle;
use App\Entity\TypeCapteur;
use App\Form\RechercheSalleType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\DetailPlanRepository;
use App\Repository\EtageRepository;
use App\Repository\SalleRepository;
use App\Repository\SARepository;
use App\Repository\ValeurCapteurRepository;
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
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SalleController extends AbstractController
{
    #[Route('/salle', name: 'app_salle_liste')]
    public function index(Request $request, SalleRepository $salleRepository, DetailPlanRepository $detailPlanRepository): Response
    {
        // Création du formulaire de recherche
        $form = $this->createForm(RechercheSalleType::class);
        $associations = $detailPlanRepository->findAll();

        $form->handleRequest($request);
        $salles = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $salleNom = $form->get('salleNom')->getData();

            if ($salleNom) {
                $salles = $salleRepository->findAll();

                $salles = array_filter($salles, function($salle) use ($salleNom) {
                    return stripos($salle->getNom(), $salleNom) !== false;
                });
            }
        } else {
            $salles = $salleRepository->findAll();
        }

        if ($salles) {
            return $this->render('salle/liste.html.twig', [
                'css' => 'salle',
                'classItem' => "salle",
                'routeItem'=> "app_salle_ajouter",
                'classSpecifique' => "BatimentEtage",
                'items' => $salles,
                'form' => $form->createView(), // Passer le formulaire à la vue

            ]);
        } else {
            return $this->render('salle/notfound.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    }

    #[Route('/salle/{id}', name: 'app_salle_infos', requirements: ['id' => '\d+'])]
    public function infos(int $id, ValeurCapteurRepository $a,SalleRepository $aRepo, DetailPlanRepository $planRepository): Response
    {
        $salle = $aRepo->find($id);
        $end = new \DateTime();
        $start = (clone $end)->modify('-1 days'); // 7 jours avant
        $arr=[];
        $val = $a->findDataForSalle2($id, $start, $end);

        if ($salle->getOnlySa() == -1){
            return $this->render('salle/infos-SansCapteur.html.twig', [
                'salle' => $salle,
            ]);
        }
        // Données des capteurs
        foreach($val as $valeur) {;
            $date=$valeur->getDateAjout()->format('Y-m-d H:i');


            switch ($valeur->getType()) {
                case TypeCapteur::TEMPERATURE:
                    $arr[$date][TypeCapteur::TEMPERATURE->value] = $valeur->getValeur();
                    break;
                case TypeCapteur::HUMIDITE:
                    $arr[$date][TypeCapteur::HUMIDITE->value] = $valeur->getValeur();
                    break;
                case TypeCapteur::LUMINOSITY:
                    $arr[$date][TypeCapteur::LUMINOSITY->value] = $valeur->getValeur();
                    break;
                case TypeCapteur::CO2:
                    $arr[$date][TypeCapteur::CO2->value] = $valeur->getValeur();
                    break;
            }
            if (!isset($latestByType[$valeur->getType()->value]) || $date > $latestByType[$valeur->getType()->value]['date']) {
                $latestByType[$valeur->getType()->value] = [
                    'valeur' => $val,
                    'date' => $date,
                ];
            }
        }



        return $this->render('salle/infos.html.twig', [
            'salle' => $salle,
            'data'=>$arr,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/salle/user', name: 'app_salle_user_liste')]
    public function indexUser(Request $request, SalleRepository $salleRepository): Response
    {
        $form = $this->createForm(RechercheSalleType::class);
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

        for ($i=0; $i<count($salles); $i++) {
            $salle = $salles[$i];

            $tempValue = null;
            $humValue = null;
            $co2Value = null;

            $response = $salleRepository->requestSalle($salle->getNom(), 1);

            $data = json_decode($response->getContent(), true);
            foreach ($data as $item) {
                if ($item['nom'] === 'temp') {
                    $tempValue = $item['valeur'];
                    $tempValue = (float)$tempValue;
                } elseif ($item['nom'] === 'hum') {
                    $humValue = $item['valeur'];
                    $humValue = (float)$humValue;
                } elseif ($item['nom'] === 'co2') {
                    $co2Value = $item['valeur'];
                    $co2Value = (float)$co2Value;
                }
            }

            $tempValue = round($tempValue, 1);
            $co2Value = round($co2Value, 0);
            $humValue = round($humValue, 1);

            if($i % 3 == 0){
                $col1[] = ['salle' => $salle, 'temp' => $tempValue, 'co2' => $co2Value, 'humi' => $humValue];
            }
            elseif($i % 3 == 1){
                $col2[] = ['salle' => $salle, 'temp' => $tempValue, 'co2' => $co2Value, 'humi' => $humValue];
            }
            elseif($i % 3 == 2){
                $col3[] = ['salle' => $salle, 'temp' => $tempValue, 'co2' => $co2Value, 'humi' => $humValue];
            }
        }

        return $this->render('salle/user_liste.html.twig', [
            'col1' => $col1,
            'col2' => $col2,
            'col3' => $col3,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/salle/user/{id}', name: 'app_salle_user_infos', requirements: ['id' => '\d+'])]
    public function infosUser(int $id, SalleRepository $salleRepository)
    {
        $salle = $salleRepository->find($id);

        $tempValue = null;
        $humValue = null;
        $co2Value = null;

        $response = $salleRepository->requestSalle($salle->getNom(), 1);
        $data = json_decode($response->getContent(), true);
        foreach ($data as $item) {
            if ($item['nom'] === 'temp') {
                $tempValue = $item['valeur'];
                $tempValue = (float)$tempValue;
            } elseif ($item['nom'] === 'hum') {
                $humValue = $item['valeur'];
                $humValue = (float)$humValue;
            } elseif ($item['nom'] === 'co2') {
                $co2Value = $item['valeur'];
                $co2Value = (float)$co2Value;
            }
        }

        $tempValue = round($tempValue, 1);
        $co2Value = round($co2Value, 0);
        $humValue = round($humValue, 1);

        $infos = ['salle' => $salle, 'temp' => $tempValue, 'co2' => $co2Value, 'humi' => $humValue];

        return $this->render('salle/user_infos.html.twig', [
            'infos' => $infos
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
    #[Route('/salle/supprimer-liees/{id}', name: 'app_salle_supprimer_liees', requirements: ['id' => '\d+'])]
    public function supprimerSallesLiees(
        int $id,
        Request $request,
        SalleRepository $salleRepository,
        BatimentRepository $batimentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer le bâtiment
        $batiment = $batimentRepository->find($id);

        // Vérifier si le bâtiment existe
        if (!$batiment) {
            $this->addFlash('error', 'Le bâtiment spécifié n\'existe pas.');
            return $this->redirectToRoute('app_batiment_liste');
        }

        // Créer le formulaire de confirmation
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => $batiment->getNom(), // Passer le nom du bâtiment comme phrase de confirmation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la saisie utilisateur
            $submittedString = $form->get('inputString')->getData();

            // Vérifier si la saisie correspond au nom du bâtiment
            if ($submittedString === $batiment->getNom()) {
                // Récupérer les salles associées
                $salles = $salleRepository->findBy(['batiment' => $batiment]);

                // Supprimer toutes les salles
                foreach ($salles as $salle) {
                    $entityManager->remove($salle);
                }
                $entityManager->flush();

                // Ajouter un message de succès
                $this->addFlash('success', 'Toutes les salles associées au bâtiment ont été supprimées.');

                // Rediriger vers la liste des bâtiments
                return $this->redirectToRoute('app_batiment_liste');
            } else {
                $this->addFlash('error', 'La saisie est incorrecte. Opération annulée.');
            }
        }

        // Afficher le formulaire
        return $this->render('salle/suppression_liees.html.twig', [
            'form' => $form->createView(),
            'batiment' => $batiment,
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
        $ids = $request->request->all('selected_salles');
        if(empty($ids)) {
            $ids = $session->get('selected_salles', []);
        }
        else
            $session->set('selected_salles', $ids);

        $salles = array_map(fn($id) => $salleRepository->find($id), $ids);
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER' // Passer la variable au formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();
            if ($submittedString=='CONFIRMER'){
                foreach ($salles as $salle ) {
                    $entityManager->remove($salle);
                }
                $entityManager->flush();
                return $this->redirectToRoute('app_salle');
            }
            else {
                $this->addFlash('error', 'La saisie est incorrect.');
            }
        }

        return $this->render('salle/supprimer.html.twig', [
            'form' => $form->createView(),
            'salles' => $salles,
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