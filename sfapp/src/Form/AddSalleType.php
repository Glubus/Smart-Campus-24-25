<?php

namespace App\Form;

use App\Entity\BatimentSalle;
use App\Entity\EtageSalle;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AddSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('batiment', ChoiceType::class, [
                'choices' => [
                    'Bâtiment D' => BatimentSalle::D,
                    'Bâtiment C' => BatimentSalle::C,
                    'Amphithéâtre' => BatimentSalle::AMPHI,
                ],
                'required' => true,
                'attr' => [
                    'class' => 'form-select',
                    'placeholder' => 'Choisir un bâtiment...',
                ],
            ])

            ->add('etage', ChoiceType::class, [
                'choices' => [
                    'Rès-de-chaussé' => EtageSalle::RESDECHAUSSE,
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
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
        ]);
    }
}
