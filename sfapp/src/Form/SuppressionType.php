<?php

namespace App\Form;

use App\Entity\BatimentSalle;
use App\Entity\EtageSalle;
use App\Entity\SA;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SuppressionSAType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $phrase = $options['phrase'];
        $builder
            ->add('inputString', TextType::class, [
                'label' => 'Entrez la phrase : '. $phrase,
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
