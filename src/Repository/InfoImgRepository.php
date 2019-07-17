<?php

namespace App\Repository;

use App\Entity\InfoImg;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InfoImg|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfoImg|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfoImg[]    findAll()
 * @method InfoImg[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfoImgRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InfoImg::class);
    }

    // /**
    //  * @return InfoImg[] Returns an array of InfoImg objects
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
    public function findOneBySomeField($value): ?InfoImg
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
