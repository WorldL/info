<?php

namespace App\Repository;

use App\Entity\ReviewFav;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReviewFav|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReviewFav|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReviewFav[]    findAll()
 * @method ReviewFav[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewFavRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReviewFav::class);
    }

    // /**
    //  * @return ReviewFav[] Returns an array of ReviewFav objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReviewFav
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
