<?php

namespace App\Form;

use App\Entity\Batiment;
use App\Entity\Plan;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
     $builder->add('nom', TextType::class, [
                        'required' => true,
                        'attr' => [
                            'placeholder' => 'Choisir un nom pour le Plan..',
                            'class' => 'form-control'
                        ],
                        'label' => "Nom du plan",
                        'label_attr' => [
                            'class' => 'form-label'
                        ],
                    ])
         ->add('Batiments', CollectionType::class, [
             'entry_type' => EntityType::class,
             'entry_options' => [
                 'class' => Batiment::class,
                 'choice_label' => 'nom',
                 'placeholder' => 'selectionner un batiment...',
                 'query_builder' => function (EntityRepository $er) {
                     return $er->createQueryBuilder('ba')
                         ->where('ba.plan IS NULL') // Use 'plan' if it is an object relation
                         ->orderBy('ba.nom', 'ASC');
                 },
                 'attr' => [
                     'class' => 'form-control sa-searchable',
                     'data-live-search' => 'true',
                 ],
                 'label' => false,
             ],
             'allow_add' => true, // Allow adding new fields dynamically
             'allow_delete' => true, // Allow removing fields dynamically
             'by_reference' => false, // Important for proper Doctrine association
             'label' => 'Batiments',
         ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plan::class,  // Associe ce formulaire à l'entité SA
        ]);
    }
}
