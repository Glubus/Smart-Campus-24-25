<?php

namespace App\Form;

use App\Entity\Batiment;
use App\Entity\SA;
use App\Entity\Salle;
use App\Form\AjoutSalleType;
use App\Form\DetailPlanType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class AjoutSAType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du SA',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'choice_label' => 'nom', // Affiche le nom des salles dans la liste déroulante
                'label' => 'Salle',
                'required' => true, // Rendre la sélection obligatoire
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.nom', 'ASC');
                },
                'placeholder' => 'Sélectionnez une salle',
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SA::class,
        ]);
    }
}
