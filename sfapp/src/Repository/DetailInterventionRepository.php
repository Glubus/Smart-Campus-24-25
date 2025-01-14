<?php

namespace App\Repository;

use App\Entity\Batiment;
use App\Entity\DetailIntervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DetailIntervention>
 */
class DetailInterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetailIntervention::class);
    }

    //    /**
    //     * @return DetailIntervention[] Returns an array of DetailIntervention objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    public function findNonTermine(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.etat != :etatTerminee')
            ->setParameter('etatTerminee', 'terminée') // Replace with the actual value representing the "Terminee" state
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByBatiment(Batiment $batiment): array
    {
        return $this->createQueryBuilder('di')
            ->join('di.salle', 's')
            ->join('s.etage', 'e')
            ->where('e.batiment = :batiment')
            ->setParameter('batiment', $batiment)
            ->orderBy('di.dateAjout', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTachesByTechnicienWithPagination( $limit, $offset)
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.dateAjout', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?DetailIntervention
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
