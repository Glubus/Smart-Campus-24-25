<?php

namespace App\Repository;

use App\Entity\SALog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SALog>
 *
 * @method SALog|null find($id, $lockMode = null, $lockVersion = null)
 * @method SALog|null findOneBy(array $criteria, array $orderBy = null)
 * @method SALog[]    findAll()
 * @method SALog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SALogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SALog::class);
    }

//    /**
//     * @return SALog[] Returns an array of SALog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SALog
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
