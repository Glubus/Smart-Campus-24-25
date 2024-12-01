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
