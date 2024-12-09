<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ApplicationType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminUserType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
  
            ->add('firstName', TextType::class, $this->getConfiguration("Prénom","Prénom de l'utilisateur"))
            ->add('lastName', TextType::class, $this->getConfiguration("Nom","Nom de famille de l'utilisateur"))
            ->add('slug', TextType::class, $this->getConfiguration("Slug","Le slug de l'utilisateur"))
            ->add('email', EmailType::class, $this->getConfiguration("Email","Adresse E-mai de l'utilisateur"))
            ->add('introduction', TextType::class, $this->getConfiguration("Introduction","Présenation rapide de l'utilisateur"))
            ->add('description', TextareaType::class, $this->getConfiguration("Description détaillée","Présenation détaillée de l'utilisateur"))
            ->add('roles', ChoiceType::class, $this->getConfiguration("Rôles",false,[
                "choices" => [
                    "Administrateur" => "ROLE_ADMIN",
                    "modérateur" => "ROLE_MODERATOR"
                ]
            ]))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
