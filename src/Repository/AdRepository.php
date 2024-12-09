<?php

namespace App\Repository;

use App\Entity\Ad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ad>
 */
class AdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ad::class);
    }

         /**
     * Permet de récupérer les meilleurs annonces
     *
     * @param int $limit
     * @return array
    */
    public function findBestAds(int $limit): array{
        return $this->createQueryBuilder('a')
                    ->select('a as annonce, AVG(c.rating) as avgRatings')
                    ->join('a.comments','c')
                    ->groupBy('a')
                    ->orderBy('avgRatings','DESC')
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    //    /**
    //     * @return Ad[] Returns an array of Ad objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ad
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
