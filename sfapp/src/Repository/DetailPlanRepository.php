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

    public function findBetweenDates(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('e') // 'e' est un alias pour votre entité
        ->where('e.dateAjout BETWEEN :startDate AND :endDate') // Filtre par dates
        ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.dateAjout', 'ASC') // Optionnel : tri par date croissante
            ->getQuery()
            ->getResult();
    }

    public function findWeeklyData(int $id, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.dateAjout BETWEEN :startDate AND :endDate AND :salle=e.salle')
            ->setParameter('startDate', $startDate)
            ->setParameter('salle',$id)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.dateAjout', 'ASC')
            ->getQuery();

        $results = $query->getResult();

        // Filtrer pour obtenir une donnée toutes les 12 heures
        $filteredResults = [];
        $lastDate = null;

        foreach ($results as $result) {
            $currentDate = $result->getDateAjout(); // Adaptation au champ de date
            if ($lastDate === null || $currentDate->getTimestamp() - $lastDate->getTimestamp() >= 14400) {
                $filteredResults[] = $result;
                $lastDate = $currentDate;
            }
        }

        return $filteredResults;
    }

    public function findMonthlyData(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.dateCapture BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.dateCapture', 'ASC')
            ->getQuery();

        $results = $query->getResult();

        // Filtrer pour obtenir une donnée par jour
        $filteredResults = [];
        $lastDay = null;

        foreach ($results as $result) {
            $currentDate = $result->getDateCapture(); // Adaptation au champ de date
            $currentDay = $currentDate->format('Y-m-d');

            if ($lastDay !== $currentDay) {
                $filteredResults[] = $result;
                $lastDay = $currentDay;
            }
        }

        return $filteredResults;
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
