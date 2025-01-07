<?php

namespace App\Repository;

use App\Entity\Subscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubscriberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscriber::class);
    }

    public function findOrCreateSubscriber(string $subscriber_id): Subscriber
    {
        
        $subscriber = $this->findOneBy(['subscriber_id' => $subscriber_id]);

        if ($subscriber instanceof Subscriber) {
            return $subscriber;
        }

        $subscriber = Subscriber::create(
            $subscriber_id, 
        );

        $this->save($subscriber);

        return $subscriber;
    }

    public function save(Subscriber $subscriber): void
    {
        $em = $this->getEntityManager();
        $em->persist($subscriber);
        $em->flush();
    }
}
