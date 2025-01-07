<?php

namespace App\Repository;

use App\Entity\EtageSalle;
use App\Entity\Salle;
use App\Entity\TypeCapteur;
use App\Entity\ValeurCapteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @extends ServiceEntityRepository<Salle>
 *
 * @method Salle|null find($id, $lockMode = null, $lockVersion = null)
 * @method Salle|null findOneBy(array $criteria, array $orderBy = null)
 * @method Salle[]    findAll()
 * @method Salle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalleRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }
    public function requestSalle(string $salle, int $page = 0): ResponseInterface
    {
        $client = HttpClient::create();

        $associations = [
            "D205" => "ESP-004",
            "D206" => "ESP-008",
            "D207" => "ESP-006",
            "D204" => "ESP-014",
            "D203" => "ESP-012",
            "D303" => "ESP-005",
            "D304" => "ESP-011",
            "C101" => "ESP-007",
            "D109" => "ESP-024",
            "Secrétariat" => "ESP-026",
            "D001" => "ESP-030",
            "D002" => "ESP-028",
            "D004" => "ESP-020",
            "C004" => "ESP-021",
            "C007" => "ESP-022"
        ];

        $db = [
            "ESP-004" => "sae34bdk1eq1",
            "ESP-008" => "sae34bdk1eq2",
            "ESP-006" => "sae34bdk1eq3",
            "ESP-014" => "sae34bdk2eq1",
            "ESP-012" => "sae34bdk2eq2",
            "ESP-005" => "sae34bdk2eq3",
            "ESP-011" => "sae34bdl1eq1",
            "ESP-007" => "sae34bdl1eq2",
            "ESP-024" => "sae34bdl1eq3",
            "ESP-026" => "sae34bdl2eq1",
            "ESP-030" => "sae34bdl2eq2",
            "ESP-028" => "sae34bdl2eq3",
            "ESP-020" => "sae34bdm1eq1",
            "ESP-021" => "sae34bdm1eq2",
            "ESP-022" => "sae34bdm1eq3"
        ];

        $headers = [        // Si l'API nécessite des en-têtes d'authentification (ex: clé API)
            'accept' => ' application/ld+json',
            'dbname' => $db[$associations[$salle]],
            'username' => 'k2eq3',
            'userpass' => 'nojsuk-kegfyh-3cyJmu'
        ];

        $url = 'https://sae34.k8s.iut-larochelle.fr/api/captures/last?nomsa='
            . $associations[$salle] . '&limit=3&page=' . $page;

        $response = $client->request('GET', $url, [
            'headers' => $headers,
        ]);

        if ($response->getStatusCode() != 200) {
            var_dump('Signaler l\'erreur au charge de mission');
            exit;
        }

        return $response;
    }

//    /**
//     * @return Salle[] Returns an array of Salle objects
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

//    public function findOneBySomeField($value): ?Salle
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
