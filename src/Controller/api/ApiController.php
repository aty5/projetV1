<?php

namespace App\Controller\api;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/lieu/{nomVille}", name="app_api_lieu", methods={"GET"})
     */
    public function listeLieu(string $nomVille, EntityManagerInterface $entityManager):Response
    {
        $ville = $entityManager->getRepository(Ville::class)->findOneBy(["nom" => $nomVille]);
        $lieux = $ville->getLieux();
        return $this->json(['lieux'=>$lieux,'CP'=>$ville->getCodePostal()],Response::HTTP_OK,[],['groups'=>'listeLieux']);
    }

    /**
     * @Route("/api/lieu/detail/{nomLieu}", name="app_api_lieu_detail", methods={"GET"})
     */
    public function detailLieu(string $nomLieu, EntityManagerInterface $entityManager):Response
    {
        $lieu = $entityManager->getRepository(Lieu::class)->findOneBy(["nom" => $nomLieu]);

        return $this->json($lieu,Response::HTTP_OK,[],['groups' => 'detailLieu']);
    }

/*
    public function majEtat(EntityManagerInterface $entityManager, SortieRepository $sortieRepository, $etatLibelle, $dateLimite, $etatDestination) {
        $query = $sortieRepository->createQueryBuilder('s');
        $query->addSelect('e')
            ->join('s.etat', 'e');
        $query->andWhere('e.libelle = :etatLibelle')
            ->andWhere('s.dateLimiteInscription <= :dateLimite')
            ->setParameter('etatLibelle', $etatLibelle)
            ->setParameter('dateLimite', $dateLimite);

        $sorties = $query->getQuery()->getResult();
        $etat = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => $etatDestination]);

        dump($sorties);

        foreach ($sorties as $sortie) {
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
        }

        $entityManager->flush();
    }
*/public function majEtatOuvertACloturee(EntityManagerInterface $entityManager, SortieRepository $sortieRepository)
    {

        $EtatOuvertACloturee = $sortieRepository->createQueryBuilder('s');
        $EtatOuvertACloturee->addSelect('e')
            ->join('s.etat', 'e');
        $EtatOuvertACloturee->andWhere('e.libelle = :etatOuvert')
            ->andWhere('s.dateLimiteInscription <= :dateJour')
            ->andWhere('s.dateHeureDebut > :dateJour')
            ->setParameter('etatOuvert', 'Ouverte')
            ->setParameter('dateJour', new \DateTime())
            ->setParameter('dateJour', new \DateTime());

        $sortieNouvellementEncours = $EtatOuvertACloturee->getQuery()->getResult();
        $etatEnCours = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Clôturée']);

        dump($sortieNouvellementEncours);

        foreach ($sortieNouvellementEncours as $sortie) {
            $sortie->setEtat($etatEnCours);
            $entityManager->persist($sortie);
        }
        $entityManager->flush();
    }

    public function majEtatClotureeAEnCours(EntityManagerInterface $entityManager, SortieRepository $sortieRepository)
    {
        // Les sorties qui doivent être en cours d'activité
        $majEtatClotureeAEnCours = $sortieRepository->createQueryBuilder('s');
        $majEtatClotureeAEnCours->addSelect('e')
            ->join('s.etat','e');
        $majEtatClotureeAEnCours->andWhere('e.libelle = :etatCloturee')
            ->andWhere('s.dateLimiteInscription < :dateJour')
            ->andWhere('s.dateHeureDebut <= :dateJour')
            ->setParameter('etatCloturee', 'Clôturée')
            ->setParameter('dateJour',new \DateTime());

        $sortiesEncours = $majEtatClotureeAEnCours->getQuery()->getResult();
        $etatEnCours = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Activité en cours']);

        dump($sortiesEncours);

        foreach ($sortiesEncours as $sortie) {
            $sortie->setEtat($etatEnCours);
            $entityManager->persist($sortie);
        }

        $entityManager->flush();
    }


        public function majEtatEnCoursAPassee(EntityManagerInterface $entityManager, SortieRepository $sortieRepository){

            $dateJour = new \DateTime();
            // Les sorties qui doivent être en cours d'activité
            $query = $sortieRepository->createQueryBuilder('s');
            $query->addSelect('e')
                ->join('s.etat', 'e');
            $query->andWhere('e.libelle = :etatEnCours')
                ->andWhere('s.dateHeureDebut <= :dateLimite')
                ->setParameter('etatEnCours', 'Activité en cours')
                ->setParameter('dateLimite', $dateJour);

            $sortiesEnCours = $query->getQuery()->getResult();
            $etatPasse = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => 'Passée']);

            dump($sortiesEnCours);

            foreach ($sortiesEnCours as $sortie) {
                $duree = new \DateInterval('PT'.$sortie->getDuree().'M');
                $dateFin = clone $sortie->getDateHeureDebut();
                $dateFin->add($duree);
                if ($dateFin <= $dateJour) {
                    $sortie->setEtat($etatPasse);
                    $entityManager->persist($sortie);
                }
            }
            $entityManager->flush();
        }




}
