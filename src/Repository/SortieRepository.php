<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    private Security $security;
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Sortie::class);
        $this->security = $security;
    }

    public function add(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function listerSortiesFiltres(SearchData $searchData): array
    {
        $user = $this->security->getUser();

        $query = $this->createQueryBuilder('s')
            ->addSelect('e')
            ->leftjoin('s.etat', 'e')
            ->addSelect('o')
            ->leftjoin('s.organisateur', 'o')
            ->addSelect('p')
            ->leftJoin('s.participants', 'p')

            ->andWhere('e.libelle != :etat_creee OR s.organisateur = :user')
            ->setParameter('etat_creee', 'créée')
            ->setParameter('user', $user);

        if(!empty($searchData->nom)){
            $query = $query
                ->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', "%{$searchData->nom}%" );
        }

        if(!empty($searchData->siteOrganisateur)){
            $query = $query
                ->andWhere('s.siteOrganisateur = :siteOrganisateur')
                ->setParameter('siteOrganisateur', $searchData->siteOrganisateur);
        }

        if ($searchData->dateDebut) {
            $query->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $searchData->dateDebut);
        }

        if ($searchData->dateFin) {
            $query->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $searchData->dateFin);
        }
        if ($searchData->inscrit) {
            $query
                ->andWhere(':user MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if ($searchData->nInscrit) {
            $query
                ->andWhere(':user NOT MEMBER OF s.participants')
                ->setParameter('user', $user);
        }

        if ($searchData->organisateur) {
            $query->andWhere('o.id = :userId')
                ->setParameter('userId', $user->getId());
        }



        // Si la case est cochée, on inclut seulement les sorties passées tout en excluant les sortiés passées depuis + d'un mois
        if ($searchData->passees) {
            $query->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime())
                ->andWhere('e.libelle = :libelle')
                ->setParameter('libelle', 'passée')
                ->andWhere('s.dateHeureDebut >= :moinsUnMois')
                ->setParameter('moinsUnMois', (new \DateTime('-1 month')));
        } else { // Sinon, on exclut toutes les sorties passées
            $query
                ->andWhere('e.libelle != :libelle')
                ->setParameter('libelle', 'passée');
        }

        return $query->getQuery()->getResult();
    }



}
