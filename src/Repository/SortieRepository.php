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
    private $security;
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
            ->leftJoin('s.participants', 'p');
            /*->addSelect('l')
            ->leftjoin('s.lieu', 'l')
            ->addSelect('sO')
            ->leftjoin('s.siteOrganisateur', 'sO')*/

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
            $query->andWhere('s.dateLimiteInscription <= :dateFin')
                ->setParameter('dateFin', $searchData->dateFin);
        }

        if ($searchData->inscrit) {
            $query->innerJoin('s.participants', 'par')
                ->andWhere('par.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        if ($searchData->organisateur) {
            $query->andWhere('o.id = :userId')
                ->setParameter('userId', $user->getId());
        }

        if ($searchData->nInscrit) {
            $query->leftJoin('s.participants', 'part')
                ->andWhere('part.id IS NULL OR part.id != :userId')
                ->setParameter('userId', $user->getId());
        }
        if (!$searchData->passees) { // Si la case n'est pas cochée, on exclut les sorties passées
            $query->andWhere('e.libelle != :libelle')
                ->setParameter('libelle', 'passée');
        }


        return $query->getQuery()->getResult();
    }


}
