<?php

namespace App\Form;
use App\Entity\Salle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('salleNom', TextType::class, [
                'label' => 'Nom de la salle: ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher par nom...'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas besoin de lier à une entité
        ]);
    }
}
