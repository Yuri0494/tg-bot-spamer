<?php

namespace App\Repository;

use App\Entity\Girl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
