<?php

namespace App\Form;

use App\Entity\BatimentSalle;
use App\Entity\EtageSalle;
use App\Entity\SA;
use App\Entity\Salle;
use App\Entity\Plan;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AssociationSASalle extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sa', EntityType::class, [
                'class' => SA::class,
                'choice_label' => 'nom',
                'label' => 'Système d\'acquisition',
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('sa')
                        ->leftJoin('sa.plan', 'plan')
                        ->where('plan.id IS NULL')  // Cette condition filtre les SA qui n'ont pas de Plan associé
                        ->orderBy('sa.nom', 'ASC'); // Trie les SA par nom, par exemple
                },
                'attr' => [
                    'class' => 'form-control sa-searchable', // Applique les classes Bootstrap
                    'data-live-search' => 'true',            // Option pour activer la recherche dans le select (pour une meilleure expérience utilisateur)
                ],
            ])
        // Champ pour la Salle
        ->add('Salle', EntityType::class, [
            'class' => Salle::class,
            'choice_label' => function (Salle $salle) {
                return $salle->getSalleNom();  // Appel à la méthode getSalleNom() de l'entité Salle
            },
            'label' => 'Salle',
            'required' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('s')
                    ->leftJoin('s.plan', 'plan')
                    ->where('plan.id IS NULL')
                    ->orderBy('s.batiment', 'ASC');  // Trie les salles par nom
            },
            'attr' => [
                'class' => 'form-control salle-searchable', // Applique les classes Bootstrap et Select2
                'data-live-search' => 'true',               // Active la recherche dans le select
            ],
        ]);



    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plan::class,

        ]);
    }
}
