<?php

namespace App\Form;

use App\Entity\Batiment;
use Symfony\Component\Form\AbstractType;
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Batiment::class,
        ]);
    }
}
