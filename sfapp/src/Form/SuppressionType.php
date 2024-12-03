<?php

namespace App\Form;

use App\Entity\BatimentSalle;
use App\Entity\EtageSalle;
use App\Entity\SA;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SuppressionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $phrase = 'Entrez la phrase : '.$options['phrase'];
        $builder
            ->add('inputString', TextType::class, [
                'label' => $phrase,
                'label_attr' => [
                    'class' => 'badge bg-warning text-dark w-100 py-2 text-center' // Classe Bootstrap pour le bandeau jaune
                ],
                'attr' => [
                    'class' => 'form-control' // Classe Bootstrap pour le champ de saisie
                ],
                'required' => true,
            ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,  // Si vous n'avez pas d'entité associée
            'phrase' => null,  // phrase est la phrase de saisis de l'utilisateur
        ]);
    }
}
