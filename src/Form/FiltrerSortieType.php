<?php

namespace App\Form;

use App\Data\SearchData;
use App\Entity\Campus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class FiltrerSortieType extends AbstractType
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('siteOrganisateur', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom',
                'required' => false,


            ])
            ->add('nom', TextType::class, [
                'label' => "Le nom de la sortie contient",
                'required' => false,
                'attr' => [
                    'placeholder' => 'search'
                ]
            ])
            ->add('dateDebut', DateType::class, [
                'label' => "Entre le",
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])
            ->add('dateFin', DateType::class, [
                'label' => "et le",
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])

            ->add('organisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false,
            ])
            ->add('inscrit', CheckboxType::class, [
                'label' => 'Sortie auxquelles je suis inscrit/e',
                'required' => false,
            ])
            ->add('nInscrit', CheckboxType::class, [
                'label' => 'Sortie auxquelles je ne suis pas inscrit/e',
                'required' => false,
            ])
            ->add('passees', CheckboxType::class, [
                'label' => 'Sortie passées',
                'required' => false,
            ])


            ;
        }


    public function configureOptions(OptionsResolver $resolver): void
    {

        $resolver->setDefaults([
            'data_class' => SearchData::class,
            'method' => 'GET', //en get car les parametres vont apparaitre dans l'urlpour partager le lien facilement, en post pas de parametre dans l'url
            'csrf_protection' => false, // revoir cours, évite d'avoir à gérer le token qui ne sert à rien dans ce cas, lors du partage de lien
        ]);
    }

    public function getBlockPrefix() //pour avoir une url + propre,
    {
        return '';
    }
}
