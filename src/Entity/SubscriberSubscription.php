<?php

namespace App\Entity;

use App\Repository\SubscriberSubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: SubscriberSubscriptionRepository::class)]
class SubscriberSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::BIGINT, length: 255)]
    private string $subscriber_id;

    #[ORM\Column]
    private int $subscription_id;

    #[ORM\Column]
    private int $last_watched_series;

    #[ORM\Column(type: Types::JSON)]
    private array $parameters = [];

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

    public function getSubscriptionId(): int
    {
        return $this->subscription_id;
    }

    public function setSubscriptionId(int $subscription_id): static
    {
        $this->subscription_id = $subscription_id;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getLastWatchedSeries(): int
    {
        return $this->last_watched_series;
    }

    public function getCurrentSeriesForWatching(int $quantity = 1): int
    {
        if ($this->last_watched_series < 1) {
            return 1;
        }

        return $this->last_watched_series + $quantity;
    }

    public function getPrevSeriesForWatching(int $quantity = 1): int
    {
        if ($this->last_watched_series - $quantity < 1) {
            return 1;
        }

        return $this->last_watched_series - $quantity;
    }

    public function setLastWatchedSeries(int $last_watched_series): static
    {
        $this->last_watched_series = $last_watched_series;

        return $this;
    }

    public static function create(Subscriber $subscriber, Subscription $subscription)
    {
        $ss = new SubscriberSubscription();
        $ss->setSubscriberId($subscriber->getSubscriberId());
        $ss->setSubscriptionId($subscription->getId());
        $ss->setLastWatchedSeries(0);

        return $ss;
    }
}
