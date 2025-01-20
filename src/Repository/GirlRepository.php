<?php

namespace App\Repository;

use App\Entity\Girl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\ParameterType;

class GirlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Girl::class);
    }

    public function getSomeUnwatchedGirls($limit, $sortConfig = ['id' => 'ASC'])
    {
        return $this->findBy(['is_watched' => false], $sortConfig, $limit);
    }

    public function getSomeGirlsFromId(int $id, $limit = 3)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.id >= (:id)')
            ->setParameter('id', $id, ParameterType::INTEGER)
            ->setMaxResults($limit)
            ->getQuery()
            ->execute();
    }
}
