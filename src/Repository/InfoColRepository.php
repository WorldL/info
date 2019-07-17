<?php

namespace App\Repository;

use App\Entity\InfoCol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InfoCol|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfoCol|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfoCol[]    findAll()
 * @method InfoCol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoColRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InfoCol::class);
    }

    // /**
    //  * @return InfoCol[] Returns an array of InfoCol objects
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
    public function findOneBySomeField($value): ?InfoCol
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
