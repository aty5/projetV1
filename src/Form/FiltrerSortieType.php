<?php

namespace App\Form;

use App\Data\SearchData;
use App\Entity\Campus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltrerSortieType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $repository = $this->entityManager->getRepository(Campus::class);
        $campus = $repository->findAll();
        $campusArray = array();
        foreach ($campus as $camp) {
            $campusArray[$camp->getNom()] = $camp->getId();
        }

        $builder
            ->add('siteOrganisateur', ChoiceType::class, [
                'label' => 'Campus',
                'choices' => $campusArray,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
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
            'method' => 'GET',
            'csrf_protection' => false,
            /*'siteOrganisateur' => [
                'Site 1' => 'Site 1',
                'Site 2' => 'Site 2',
                'Site 3' => 'Site 3',
            ]*/
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
