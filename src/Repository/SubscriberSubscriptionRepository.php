<?php

namespace App\Repository;

use App\Entity\SubscriberSubscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Subscriber;
use App\Entity\Subscription;

class SubscriberSubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private SubscriberRepository $subscriberRepository,
        ManagerRegistry $registry
        )
    {
        parent::__construct($registry, SubscriberSubscription::class);
    }

    public function saveNewRecord(Subscriber $subscriber, Subscription $subscription)
    {
        $ss = SubscriberSubscription::create($subscriber, $subscription);
        $this->save($ss);
    }

    public function save(SubscriberSubscription $ss): void
    {
        $em = $this->getEntityManager();
        $em->persist($ss);
        $em->flush();
    }

    public function delete(SubscriberSubscription $ss): void
    {
        $em = $this->getEntityManager();
        $em->remove($ss);
        $em->flush();
    }

    public function getSubscriptionIdsOfSubscriber(string $id)
    {
        $qr = $this->createQueryBuilder('ss')
            ->select('ss.subscription_id')
            ->where('ss.subscriber_id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult();

        $result = [];

        foreach($qr as $element) {
            $result[] = $element['subscription_id'];
        }

        return $result;
    }

    public function getActiveSubscribersIds()
    {
        $qr = $this->createQueryBuilder('ss')
            ->select('ss.subscriber_id')
            ->getQuery()
            ->getArrayResult();

        $result = [];

        foreach($qr as $element) {
            $result[] = $element['subscriber_id'];
        }

        return array_unique($result);
    }

    public function getSubscriptionsOfSubscriber(string $id)
    {
        $ss = $this->getSubscriptionIdsOfSubscriber($id);

        return $this->subscriptionRepository->findBy(['id' => $ss]);
    }

    public function getForTest()
    {
        return $this->findBy(['subscriber_id' => [-4730808071, 788788415]]);
    }
}
