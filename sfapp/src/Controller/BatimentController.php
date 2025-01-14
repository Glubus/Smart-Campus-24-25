<?php

namespace App\Controller;

use App\Entity\Batiment;
use App\Entity\SA;
use App\Entity\Plan;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\DetailPlan;
use App\Form\AjoutBatimentType;
use App\Form\SuppressionType;
use App\Repository\BatimentRepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function PHPUnit\Framework\isNull;

class   BatimentController extends AbstractController
{
    private const CONFIRMATION_PHRASE = 'CONFIRMER';

    #[Route('/batiment', name: 'app_batiment_liste')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        $batiments = $entityManager->getRepository(Batiment::class)->findAll();
        $index = 0;
        $col1 = [];
        $col2 = [];
        $col3 = [];
        foreach ($batiments as $batiment) {
            if($index%2 == 0){
                $col1[] = $batiment;
            } elseif ($index%2 == 1) {
                $col2[] = $batiment;
            } else {
                $col3[] = $batiment;
            }
            $index++;
        }

        return $this->render('batiment/liste.html.twig', [
            'col1' => $col1,
            'col2' => $col2,
            'col3' => $col3,
        ]);
        /*
        return $this->render('batiment/liste.html.twig', [
            'css' => 'batiment',
            'classItem' => "batiment",
            'items' => $batiments,
            'routeAjouter' => "app_batiment_ajouter",
            'classSpecifique' => ""
        ]);*/
    }

    #[Route('/batiment/{id}', name: 'app_batiment_infos', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function infos(int $id, BatimentRepository $repository): Response
    {
        $batiment = $repository->find($id);

        return $this->render('batiment/infos.html.twig', [
            'css' => 'batiment',
            'classItem' => "batiment",
            'item' => $batiment,
            'routeAjouter' => "app_batiment_ajouter",
            'classSpecifique' => ""
        ]);
    }
    #[Route('/batiment/modifier/{id}', name: 'app_batiment_modifier')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function modifier(int $id, Request $request, BatimentRepository $batimentRepository, EntityManagerInterface $em): Response
    {
        $batiment = $batimentRepository->find($id);

        return $this->render('batiment/ajouter.html.twig', [
            'css' => 'batiment',
            'classItem' => "batiment",
            'item' => $batiment,
            'routeItem'=> "app_batiment_modifier",
            'classSpecifique' => ""
        ]);
    }

    #[Route('/batiment/ajouter', name: 'app_batiment_ajouter')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function ajouter(Request $request, BatimentRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $batimentId = $request->get('batiment');
        $batiment = $batimentId ? $repository->find($batimentId) : new Batiment();

        $form = $this->createForm(AjoutBatimentType::class, $batiment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Access dynamically added "etages" data
            $etages = $request->request->all('form')['etages']; // Safely retrieve
            foreach ($etages as $key => $etageName) {
                if($etageName != null){
                    $batiment->renameEtage($key, $etageName);
                }
                else
                    $etages[$key] = $key;
            }

            if (!$this->isNomEtagesUnique($etages)) {
                $this->addFlash('error', 'Chaque étage doit avoir un nom unique.');
            } elseif ($repository->findOneBy(['nom' => $batiment->getNom()])) {
                $this->addFlash('error', 'Ce bâtiment existe déjà.');
            } else {
                $this->processEtages($batiment, $etages);
                $this->persistAndFlush($entityManager, $batiment);

                return $this->redirectToRoute('app_batiment_liste');
            }
        }

        return $this->render('batiment/ajouter.html.twig', [
            'form' => $form->createView(),
            'css' => 'batiment',
            'classItem' => "batiment",
            'routeAjouter' => "app_batiment_ajouter",
            'classSpecifique' => ""
        ]);
    }

    #[Route('/batiment/{id}/suppression', name: 'app_batiment_suppression')]
    #[IsGranted('ROLE_CHARGE_DE_MISSION')]
    public function supprimer(Request $request, BatimentRepository $repository, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $selectedIds = $this->getSelectedIds($request, $session, 'selected_batiment');
        $batiments = $this->getSelectedBatiments($selectedIds, $repository);

        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => self::CONFIRMATION_PHRASE
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $this->isConfirmationValid($form->get('inputString')->getData())) {
            $this->deleteBatiments($batiments, $entityManager);

            return $this->redirectToRoute('app_batiment_liste');
        }

        $this->addFlash('error', 'La saisie est incorrecte.');

        return $this->render('batiment/supprimer.html.twig', [
            'form' => $form->createView(),
            'batiments' => $batiments,
        ]);
    }

    private function processEtages(Batiment $batiment, array $etages): void
    {
        foreach ($etages as $key => $etageName) {
            if ($etageName !== null) {
                $batiment->renameEtage($key, $etageName);
            }
        }
    }

    private function deleteBatiments(array $batiments, EntityManagerInterface $entityManager): void
    {
        foreach ($batiments as $batiment) {
            foreach ($batiment->getEtages() as $etage) {
                foreach ($etage->getSalles() as $salle) {
                    $entityManager->remove($salle);
                }
            }
            $entityManager->remove($batiment);
        }
        $entityManager->flush();
    }

    private function getSelectedIds(Request $request, SessionInterface $session, string $sessionKey): array
    {
        $ids = $request->query->all('selected_batiment') ?: $session->get($sessionKey, []);
        $session->set($sessionKey, $ids);

        return $ids;
    }

    private function getSelectedBatiments(array $ids, BatimentRepository $repository): array
    {
        return array_filter(array_map(fn($id) => $repository->find($id), $ids));
    }

    private function isNomEtagesUnique(array $etages): bool
    {
        return count($etages) === count(array_unique($etages));
    }

    private function isConfirmationValid(string $submittedString): bool
    {
        return $submittedString === self::CONFIRMATION_PHRASE;
    }

    private function persistAndFlush(EntityManagerInterface $entityManager, object $entity): void
    {
        $entityManager->persist($entity);
        $entityManager->flush();
    }
    #[Route('/batiment/{id}/max-etages', name: 'batiment_max_etages', methods: ['GET'])]
    public function getMaxEtages(int $id, BatimentRepository $batimentRepository): JsonResponse
    {
        // Récupérer le bâtiment par son ID
        $batiment = $batimentRepository->find($id);

        // Si le bâtiment n'existe pas, renvoyer une erreur 404
        if (!$batiment) {
            throw new NotFoundHttpException('Bâtiment non trouvé.');
        }

        // Supposons que l'entité Batiment a une méthode getNombreEtagesMax()
        $maxEtages = $batiment->getNbEtages();

        // Retourner les données en JSON
        return new JsonResponse(['maxEtages' => $maxEtages]);
    }
    #[Route('/batiment/supprimer-selection', name: 'app_batiment_supprimer_selection', methods: ['POST', 'GET'])]


    public function suppSelection(
        Request $request,
        BatimentRepository $batimentRepository,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        // Récupération des bâtiments sélectionnés
        $ids = $this->getSelectedBatimentIds($request, $session);
        $batiments = $this->getBatimentsByIds($ids, $batimentRepository);

        // Création du formulaire
        $form = $this->createForm(SuppressionType::class, null, [
            'phrase' => 'CONFIRMER'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedString = $form->get('inputString')->getData();

            if ($this->isConfirmationValid($submittedString)) {
                $this->deleteBatiments($batiments, $entityManager);

                return $this->redirectToRoute('app_batiment_liste');
            }

            $this->addFlash('error', 'La saisie est incorrecte.');
        }

        return $this->render('batiment/supprimer_multiple.html.twig', [
            'form' => $form->createView(),
            'items' => $batiments,
            'classItem' => 'batiment'
        ]);
    }

    private function getSelectedBatimentIds(Request $request, SessionInterface $session): array
    {
        $ids = $request->request->all('selected') ?: $session->get('selected', []);
        $session->set('selected', $ids);

        return $ids;
    }

    private function getBatimentsByIds(array $ids, BatimentRepository $batimentRepository): array
    {
        return array_filter(
            array_map(fn($id) => $batimentRepository->find($id), $ids),
            fn($batiment) => $batiment !== null
        );
    }


}
