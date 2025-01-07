<?php

namespace App\Entity;

use App\Repository\SubscriberRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: SubscriberRepository::class)]
class Subscriber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::BIGINT, length: 255, unique: true)]
    private string $subscriber_id;

    public function getId(): int
    {
        return $this->id;
    }
    
    public function getSubscriberId(): string
    {
        return $this->subscriber_id;
    }

    public function setSubscriberId(string $subscriber_id): static
    {
        $this->subscriber_id = $subscriber_id;

        return $this;
    }

    public static function create(string $subscriber_id)
    {
        $subscriber = new Subscriber();
        $subscriber->subscriber_id = $subscriber_id;

        return $subscriber;
    }
}
