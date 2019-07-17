<?php

namespace App\Repository;

use App\Entity\InfoImgTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InfoImgTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfoImgTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfoImgTag[]    findAll()
 * @method InfoImgTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoImgTagRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InfoImgTag::class);
    }

    // /**
    //  * @return InfoImgTag[] Returns an array of InfoImgTag objects
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
    public function findOneBySomeField($value): ?InfoImgTag
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
