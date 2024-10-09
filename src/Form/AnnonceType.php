<?php

namespace App\Form;

use App\Entity\Ad;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AnnonceType extends AbstractType
{
    private function getConfiguration(string $label, string $placeholder, array $options = []): array
    {
        return array_merge_recursive([
            'label' => $label,
            'attr' => [
                'placeholder' => $placeholder
            ]],
            $options
        );  
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => "Titre de votre annonce"
                ]
            ])
            ->add('slug', TextType::class, $this->getConfiguration('Slug','Adresse web (automatique)',[
                'required' => false
                ]))
            ->add('coverImage', UrlType::class,$this->getConfiguration("Url de l'image", "Donnez l'adresse de votre image"))
            ->add('introduction', TextType::class, $this->getConfiguration("Introduction","Donnez une description globale de votre annonce"))
            ->add('content', TextareaType::class, $this->getConfiguration("Description détaillée", "Donnez une description de votre bien"))
            ->add('rooms', IntegerType::class, $this->getConfiguration("Nombre de chambre","Donnez le nombre de chambres disponibles"))
            ->add('price', MoneyType::class, $this->getConfiguration("Prix par nuit","Indiquez le prix que vous voulez pour une nuit"))
            // ->add('save', SubmitType::class, ['label' => 'Envoyer'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
