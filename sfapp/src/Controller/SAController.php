<?php

namespace App\Controller;

use App\Entity\SA;
use App\Entity\Capteur;
use App\Entity\Plan;
use App\Entity\TypeCapteur;
use App\Form\AjoutSAType;
use App\Form\SuppressionType;
use App\Repository\SARepository;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SAController extends AbstractController
{
    #[Route('/sa/ajout', name: 'app_sa_ajout')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création du nouvel objet SA
        $SA = new SA();

        // Création du formulaire
        $form = $this->createForm(AjoutSAType::class, $SA);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $SA->setDateAjout(new \DateTime());
            // Vérifier si le nom est unique
            $existingSA = $entityManager->getRepository(SA::class)->findOneBy(['nom' => $SA->getNom()]);
            if ($existingSA) {
                $this->addFlash('error', 'Le nom saisi est déjà utilisé.');
            } else {
                // Persister l'entité SA
                $entityManager->persist($SA);

                // Créer et associer 3 capteurs à cet SA
                for ($i = 1; $i <= 3; $i++) {
                    $type = TypeCapteur::cases()[$i-1];
                    $capteur = new Capteur();
                    $capteur->setNom('Capteur ' .$i .' '. $SA->getNom())
                        ->setType($type)
                        ->setSA($SA);

                    $entityManager->persist($capteur);
                }

                // Sauvegarder dans la base de données
                $entityManager->flush();

                $this->addFlash('success', 'SA et ses capteurs associés ont été ajoutés avec succès.');

                // Redirection vers la liste des SA
                return $this->redirectToRoute('app_sa_liste');
            }
        }

        // Affichage du formulaire
        return $this->render('sa/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/sa', name: 'app_sa_liste')]
    public function lister(SARepository $saRepo): Response
    {
        $sa = $saRepo->findAll();
        return $this->render('sa/liste.html.twig', [
            "liste_SA" => $sa,
        ]);
    }

    #[Route('/sa/{id}/suppression', name: 'app_sa_suppression')]
    public function supprimer(Request $request, int $id, SARepository $repo, EntityManagerInterface $em): Response
    {
        $SA = $repo->find($id);
        if ($SA) {
            $form = $this->createForm(SuppressionType::class, null, [
                'phrase' => $SA->getNom(), // Passer la variable au formulaire
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $submittedString = $form->get('inputString')->getData();
                if ($submittedString == $SA->getNom()) {
                    $em->remove($SA);
                    $em->flush();
                    $this->addFlash('success', 'SA supprimé avec succès.');
                    return $this->redirectToRoute('app_sa_liste');
                } else {
                    $this->addFlash('error', 'La saisie est incorrecte.');
                }
            }

            return $this->render('sa/suppression.html.twig', [
                "form" => $form->createView(),
                "SA" => $SA,
            ]);
        }

        return $this->render('sa/notfound.html.twig', []);
    }

    #[Route('/sa/{id}', name: 'app_sa_infos')]
    public function affichage_SA(Request $request, int $id, SARepository $repo,EntityManagerInterface $entityManager): Response
    {
        $SA = $repo->find($id);
        if (!$SA) {
            throw $this->createNotFoundException('SA introuvable.');
        }
        // trouver la salle d'un Sa
        $plan = $entityManager->getRepository(Plan::class)->findOneBy(['sa' => $SA]);
        $salle = $plan ? $plan->getSalle() : null;

        return $this->render('sa/info.html.twig', [
            "SA" => $SA,
            "salle" => $salle,
        ]);
    }
}
