<?php

namespace App\Repository;

use App\Entity\Sketches;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SketchesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sketches::class);
    }

    public function findOne(int $seriesNumber, string $name): ?Sketches
    {
        return $this->findOneBy([
            'series_number' => $seriesNumber,
            'sketch_name' => $name,
        ]);
    }

    public function findFirstUnwatched(string $sketch_name): ?Sketches
    {
        return $this->findOneBy(['sketch_name' => $sketch_name, 'isWatched' => false], ['series_number' => 'ASC']);
    }

    public function findByExampleField($value): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
}
