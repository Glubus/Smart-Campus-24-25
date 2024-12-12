<?php

namespace App\Form;
use App\Entity\Salle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class RechercheSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('salleNom', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Rechercher par nom...'
                ],
            ])
            ->add('rechercher', ButtonType::class, [
                'label' => 'Rechercher',
                'attr' => [
                    'type' => 'submit',
                    'style' => 'flex-grow: 1;
                                margin-left: 17px;
                                height: 100%;
                                background-color: #A0A0A0;
                                border-radius: 10px;
                                color: white;
                                border: none;',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas besoin de lier à une entité
        ]);
    }
}
