<?php

namespace App\Form;

use App\Entity\Batiment;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
                'label_attr' => ['class' => 'form-label text-primary'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => true,
            ])
            ->add('etage', TextType::class, [
                'label' => 'Étage',
                'label_attr' => ['class' => 'form-label text-primary'],
                'attr' => [
                    'class' => 'form-control',
                ],
                'required' => true,
            ])
            // Champ Numero (TextType)
            ->add('nom', TextType::class, [
                'label' => 'Nom de salle',
                'label_attr' => [
                    'class' => 'form-label text-primary'  // Classe pour le label
                ],
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => '20',  // Limite le nombre de caractères
                    'placeholder' => 'Ex : D101',  // Placeholder du champ
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
