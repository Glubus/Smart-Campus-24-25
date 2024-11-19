<?php

namespace App\Form;

use App\Entity\Batiment;
use App\Entity\EtageSalle;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ Batiment (EntityType)
            ->add('batiment', EntityType::class, [
                'class' => Batiment::class,
                'choice_label' => 'nom', // Assumes 'nom' is a property of 'Batiment'
                'label' => 'Batiment',
                'label_attr' => [
                    'class' => 'form-label text-primary'  // Classe pour le label
                ],
                'attr' => [
                    'class' => 'form-control'  // Classe pour le champ
                ],
                'required' => true,
            ])

            // Champ Etage (ChoiceType)
            ->add('etage', ChoiceType::class, [
                'choices' => [
                    'Rez-de-chaussée' => EtageSalle::REZDECHAUSSEE,
                    '1' => EtageSalle::PREMIER,
                    '2' => EtageSalle::DEUXIEME,
                    '3' => EtageSalle::TROISIEME,
                ],
                'label' => 'Etage',
                'label_attr' => [
                    'class' => 'form-label text-primary'  // Classe pour le label
                ],
                'attr' => [
                    'class' => 'form-select'  // Classe pour le champ select
                ],
                'required' => true,
            ])

            // Champ Numero (TextType)
            ->add('numero', TextType::class, [
                'label' => 'Numéro de salle',
                'label_attr' => [
                    'class' => 'form-label text-primary'  // Classe pour le label
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => '2',  // Limite le nombre de caractères
                    'placeholder' => 'Ex. 01',  // Placeholder du champ
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,  // Associe ce formulaire à l'entité Salle
        ]);
    }
}
