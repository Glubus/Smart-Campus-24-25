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
     $builder->add('nom', TextType::class, [
                        'required' => true,
                        'attr' => [
                            'placeholder' => 'Choisir un nom pour le Plan..',
                            'class' => 'form-control'
                        ],
                        'label' => "Nom du plan",
                        'label_attr' => [
                            'class' => 'form-label'
                        ],
                    ])
            ->add('Batiments', EntityType::class, [
                'class' => Batiment::class, // Class of the entity
                'choice_label' => 'nom',   // Field to be displayed for each option (the name of the building)
                'multiple' => true,        // Allows multiple selections
                'expanded' => true,        // If you want checkboxes instead of a select dropdown
                'placeholder' => 'Selectionner des bâtiments...', // Placeholder
                'query_builder' => function (EntityRepository $er) {
                    // Create the query to retrieve the Batiments
                    return $er->createQueryBuilder('ba')
                        ->where('ba.plan IS NULL') // Condition for the Batiments
                        ->orderBy('ba.nom', 'ASC'); // Ordering the Batiments by name
                },
                'label' => 'Bâtiments',  // Label for the field
                'attr' => [
                    'class' => 'form-control sa-searchable', // Optional: Add custom styles
                    'data-live-search' => 'true', // Optional: Add live search
                    'style' => 'margin-left: 10px; ',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plan::class,  // Associe ce formulaire à l'entité SA
        ]);
    }
}
