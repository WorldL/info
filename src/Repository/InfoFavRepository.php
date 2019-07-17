<?php

namespace App\Repository;

use App\Entity\InfoFav;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InfoFav|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfoFav|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfoFav[]    findAll()
 * @method InfoFav[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoFavRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InfoFav::class);
    }

    // /**
    //  * @return InfoFav[] Returns an array of InfoFav objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InfoFav
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
