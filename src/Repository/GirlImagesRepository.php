<?php

namespace App\Repository;

use App\Entity\GirlImages;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GirlImages>
 *
 * @method GirlImages|null find($id, $lockMode = null, $lockVersion = null)
 * @method GirlImages|null findOneBy(array $criteria, array $orderBy = null)
 * @method GirlImages[]    findAll()
 * @method GirlImages[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GirlImagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GirlImages::class);
    }

//    /**
//     * @return GirlImages[] Returns an array of GirlImages objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GirlImages
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
