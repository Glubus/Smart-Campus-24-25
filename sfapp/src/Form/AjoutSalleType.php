<?php

namespace App\Form;

use App\Entity\Batiment;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ Batiment (EntityType)
            ->add('batiment', EntityType::class, [
                'class' => Batiment::class,
                'choice_label' => 'nom',
                'label' => 'Bâtiment',
                'label_attr' => [
                    'class' => 'form-label text-primary',
                    'style' => 'margin-top: 10px;',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: 80%;', // Style en ligne pour ajuster la largeur
                    'data-action' => 'update-max-etages',
                ],
                'required' => true,
            ])
            ->add('add_batiment', ButtonType::class, [
                'label' => 'Ajouter un bâtiment',
                'attr' => [
                    'class' => 'btn btn-secondary',
                    'style' => 'margin-top: 10px;', // Style en ligne pour alignement
                    'onclick' => "window.location.href='/batiment/ajout'",
                ],
            ])
            ->add('etage', TextType::class, [
                'label' => 'Étage',
                'label_attr' => [
                    'class' => 'form-label text-primary',
                    'style' => 'margin-top: 10px;',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: 100%;', // Largeur par défaut
                    'data-max-etage' => '10',
                ],
                'required' => true,
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom de salle',
                'label_attr' => [
                    'class' => 'form-label text-primary',
                    'style' => 'margin-top: 10px;',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: 100%;',
                    'maxlength' => '20',
                    'placeholder' => 'Ex : D101',
                ],
                'required' => true,
            ])
            ->add('fenetre', TextType::class, [
                'label' => 'Nombre de fenêtre (optionnel)',
                'label_attr' => [
                    'class' => 'form-label text-primary',
                    'style' => 'margin-top: 10px;',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: 48%; margin-right: 2%;', // Champs alignés côte à côte
                    'maxlength' => '20',
                    'placeholder' => '10',
                ],
                'required' => false,
            ])
            ->add('radiateur', TextType::class, [
                'label' => 'Nombre de radiateur (optionnel)',
                'label_attr' => [
                    'class' => 'form-label text-primary',
                    'style' => 'margin-top: 10px;',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'width: 48%;', // Champs alignés côte à côte
                    'maxlength' => '20',
                    'placeholder' => '10',
                ],
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,  // Associe ce formulaire à l'entité Salle
        ]);
    }
}
