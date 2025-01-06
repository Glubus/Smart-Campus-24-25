<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom',
                ],
                'label' => 'Nom',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('prenom', null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre prénom',
                ],
                'label' => 'Prénom',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('adresse',null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'adresse',
                ],
                'label' => 'Adresse',
                'label_attr' => ['class' => 'form-label']
            ])
            ->add('email',null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'email',
                ],
                'label' => 'Email',
                'label_attr' => ['class' => 'form-label']])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Charge de mission' => 'ROLE_CHARGE_DE_MISSION',
                    'Technicien' => 'ROLE_TECHNICIEN',
                ],
                'expanded' => false, // Liste déroulante
                'multiple' => false, // Une seule option sélectionnable
                'mapped' => false, // Empêche le champ d'être directement lié à l'entité
                'attr' => [
                    'class' => 'form-select', // Style Bootstrap
                ],
                'label' => 'Rôle',
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Entrez un mot de passe',
                ],
                'label' => 'Mot de passe',
                'label_attr' => ['class' => 'form-label'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
