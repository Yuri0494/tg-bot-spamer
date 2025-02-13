<?php

namespace App\Repository;

use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\ArrayParameterType;

class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function getSubscriptionsNames(array $ids)
    {
        $qr = $this->createQueryBuilder('s')
            ->select('s.name')
            ->andWhere('s.id IN (:id)')
            ->setParameter('id', $ids, ArrayParameterType::INTEGER)
            ->getQuery()
            ->getArrayResult();

        $result = [];

        foreach($qr as $element) {
            $result[] = $element['name'];
        }

        return $result;
    }

    public function create(string $name, string $code, string $category)
    {
        $subscription = Subscription::create($name, $code, $category);
        $this->save($subscription);
    }

    public function save(Subscription $subscription): void
    {
        $em = $this->getEntityManager();
        $em->persist($subscription);
        $em->flush();
    }
}
