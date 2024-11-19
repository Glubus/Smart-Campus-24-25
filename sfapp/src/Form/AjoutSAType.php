<?php

namespace App\Form;

use App\Entity\SA;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutSAType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ Nom
            ->add('nom', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Choisir un nom de SA..',
                ],
                'label' => 'Nom du système d\'acquisition',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SA::class,  // Associe ce formulaire à l'entité SA
        ]);
    }
}
