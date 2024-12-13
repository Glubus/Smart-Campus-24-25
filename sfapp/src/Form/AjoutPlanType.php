<?php

namespace App\Form;

use App\Entity\Batiment;
use App\Entity\Plan;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
     $builder->add('Batiment', EntityType::class, [
                        'class' => Batiment::class,
                        'choice_label' => 'nom',
                        'label' => 'Batiment',
                        'required' => true,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('ba')// Cette condition filtre les SA qui n'ont pas de DetailPlan associé
                            ->orderBy('ba.nom', 'ASC'); // Trie les SA par nom, par exemple
                        },
                        'attr' => [
                            'class' => 'form-control sa-searchable', // Applique les classes Bootstrap
                            'data-live-search' => 'true',            // Option pour activer la recherche dans le select (pour une meilleure expérience utilisateur)
                        ],
                    ])
                    ->add('nom', TextType::class, [
                        'required' => true,
                        'attr' => [
                            'placeholder' => 'Choisir un nom pour le Plan..',
                        ],
                        'label' => "Nom du plan",
                        'label_attr' => [
                            'class' => 'form-label'
                        ],
                        'attr' => [
                            'class' => 'form-control'
                        ],
                    ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plan::class,  // Associe ce formulaire à l'entité SA
        ]);
    }
}
