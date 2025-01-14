<?php

namespace App\Form;

use App\Entity\DetailPlan;
use App\Entity\Plan;
use App\Entity\SA;
use App\Entity\Salle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DetailPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateAjout', null, [
                'widget' => 'single_text',
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'id',
            ])
            ->add('sa', EntityType::class, [
                'class' => SA::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetailPlan::class,
        ]);
    }
}

