<?php

namespace App\Repository;

use App\Entity\EtageSalle;
use App\Entity\DetailPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DetailPlan>
 *
 * @method DetailPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetailPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetailPlan[]    findAll()
 * @method DetailPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetailPlan::class);
    }
    public function findSallesRezDeChaussee()
    {
        return $this->createQueryBuilder('p')
            ->join('p.salle', 's') // Jointure avec l'entité Salle
            ->where('s.etage = :etage') // Filtrer par étage dans Salle
            ->setParameter('etage', EtageSalle::REZDECHAUSSEE)
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return DetailPlan[] Returns an array of DetailPlan objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DetailPlan
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
