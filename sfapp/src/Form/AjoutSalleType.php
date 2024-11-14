<?php

namespace App\Form;

use App\Entity\Batiment;
use App\Entity\BatimentSalle;
use App\Entity\EtageSalle;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;



class AjoutSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('batiment', EntityType::class, [
                'class' => Batiment::class,
                'choice_label' => 'nom', // Assumes 'nom' is a property of 'Usager'
                'label' => 'Batiment',
                'required' => true,
            ])
            ->add('etage', ChoiceType::class, [
                'choices' => [
                    'Rez-de-chaussée' => EtageSalle::REZDECHAUSSEE,
                    '1' => EtageSalle::PREMIER,
                    '2' => EtageSalle::DEUXIEME,
                    '3' => EtageSalle::TROISIEME,
                ],
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                    'placeholder' => 'Choisir un étage...',
                ],
            ])
            ->add('numero', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Choisir un numero...',
                    'maxlength' => 2,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
        ]);
    }
}
