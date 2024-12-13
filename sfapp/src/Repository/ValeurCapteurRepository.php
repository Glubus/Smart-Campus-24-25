<?php

namespace App\Repository;

use App\Entity\ValeurCapteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ValeurCapteur>
 *
 * @method ValeurCapteur|null find($id, $lockMode = null, $lockVersion = null)
 * @method ValeurCapteur|null findOneBy(array $criteria, array $orderBy = null)
 * @method ValeurCapteur[]    findAll()
 * @method ValeurCapteur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValeurCapteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ValeurCapteur::class);
    }



    public function findDataForSalle(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('e') // 'e' est un alias pour votre entité
        ->where('e.dateAjout BETWEEN :startDate AND :endDate') // Filtre par dates
        ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.dateAjout', 'ASC') // Optionnel : tri par date croissante
            ->getQuery()
            ->getResult();
    }

    public function findDataForSalle2(int $id, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.dateAjout BETWEEN :startDate AND :endDate AND :Salle=e.Salle')
            ->setParameter('startDate', $startDate)
            ->setParameter('Salle',$id)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.dateAjout', 'ASC')
            ->getQuery();

        $results = $query->getResult();
        return $results;
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
//     * @return ValeurCapteur[] Returns an array of ValeurCapteur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ValeurCapteur
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
