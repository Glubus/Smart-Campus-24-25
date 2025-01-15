<?php

namespace App\Form;

use App\Entity\Batiment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutBatimentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du batîment',
                'label_attr' => [
                    'class' => 'form-label text-primary'  // Classe pour le label
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex. C',  // Placeholder du champ
                ],
                'required' => true,
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'label_attr' => [
                    'class' => 'form-label text-primary'  // Classe pour le label
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '15, Rue de Francois de Vaux de Foletier',  // Placeholder du champ
                ],
                'required' => true,
            ])
            ->add('nbEtages', ChoiceType::class, [
                'label' => 'Nombre de niveaux',
                'label_attr' => ['class' => 'form-label text-primary'],
                'choices' => array_combine(range(1, 10), range(1, 10)), // Dropdown with values 1 to 100
                'attr' => ['class' => 'form-select'],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Batiment::class,
            'allow_extra_fields' => true,
        ]);
    }
}
