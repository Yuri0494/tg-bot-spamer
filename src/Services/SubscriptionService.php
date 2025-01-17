<?php

namespace App\Services;

use App\Entity\Chat;
use App\Entity\SubscriberSubscription;
use App\Entity\Subscription;
use App\TelegramBot\TelegramBot;
use App\Repository\SubscriberRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\SubscriberSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Server\Server;
use Exception;

class SubscriptionService {
    const TEST_CHAT_ID = -1002337503652;

    public function __construct(
        private SketchService $skecthService,
        private GirlService $girlService,
        private SubscriberRepository $subscriberRepository,
        private SubscriptionRepository $subscriptionRepository,
        private SubscriberSubscriptionRepository $ssRepository,
        private EntityManagerInterface $em,
        private ContentServiceFabric $serviceFabric,
        private LoggerInterface $logger,
        private TelegramBot $bot,
    ) {}
    
    public function sendHello($id, $message)
    {
        $this->bot->api->sendMessage($id, $message);
    }

    public function sendContentToSubscribers()
    {
        $allSubscriberSubscription = $this->ssRepository->findAll();

        if (empty($allSubscriberSubscription)) {
            return;
        }

        $toSend = $this->prepareDataForCommonSend($allSubscriberSubscription);

        foreach($toSend as $subscriberId => $subscriptions) {
            $this->sendHello($subscriberId, 'Привет! :)');
            foreach($subscriptions as $ss) {
                $subscription = $this->subscriptionRepository->findOneBy(['id' => $ss->getSubscriptionId()]);
                $this->sendSubscription($subscription, $ss, true);
            }
        }
    }

    public function sendSubscription(Subscription $subscription, SubscriberSubscription $ss, $needSleep = false)
    {
        $category = $this->getSubscriptionCategory($subscription);
        $quantity = $category === 'poll' ? 3 : 1;
        $sleepTime = $category === 'poll' ? 30 : 1;
        $contentService = $this->serviceFabric->getContentService($category)->setParameters($subscription, $quantity, $sleepTime, false);
    
        if ($contentService->send($ss->getSubscriberId(), $ss->getLastWatchedSeries() + 1)) {
            $ss->setLastWatchedSeries($ss->getLastWatchedSeries() + $quantity);
            $this->ssRepository->save($ss);
        } else {
            $this->ssRepository->delete($ss);
        }

        if ($needSleep) {
            sleep(10);
        }
    }

    public function sendNext(Subscription $subscription, SubscriberSubscription $ss, $needSleep = false)
    {
        $quantity = 1;
        $category = $this->getSubscriptionCategory($subscription);
        $contentService = $this->serviceFabric->getContentService($category)->setParameters($subscription, $quantity);
        $current = $ss->getLastWatchedSeries() + 1;
    
        if ($contentService->send($ss->getSubscriberId(), $ss->getLastWatchedSeries() + 1)) {
            $ss->setLastWatchedSeries($current);
            $this->ssRepository->save($ss);
        } else {
            $this->ssRepository->delete($ss);
        }

        if ($needSleep) {
            sleep(10);
        }
    }

    public function sendPrevSubscription(Subscription $subscription, SubscriberSubscription $ss)
    {
        $quantity = 1;
        $category = $this->getSubscriptionCategory($subscription);
        $contentService = $this->serviceFabric->getContentService($category)->setParameters($subscription, $quantity);
        $number = ($ss->getLastWatchedSeries() - $quantity) < 1 
                    ? 1
                    : $ss->getLastWatchedSeries() - $quantity;
    
        if ($contentService->send($ss->getSubscriberId(), $number)) {
            $ss->setLastWatchedSeries($number);
            $this->ssRepository->save($ss);
        } else {
            $this->ssRepository->delete($ss);
        }
        sleep(2);
    }

    public function sendCurrent(Subscription $subscription, SubscriberSubscription $ss, int $number)
    {
        $quantity = 1;
        $category = $this->getSubscriptionCategory($subscription);
        $contentService = $this->serviceFabric->getContentService($category)->setParameters($subscription, $quantity);
        $contentService->send($ss->getSubscriberId(), $number);
    }

    public function getAvailableSubscriptions(Chat $chat)
    {
        $subscriber = $this->subscriberRepository->findOrCreateSubscriber($chat->getChatId());
        $allSubscriptions = $this->subscriptionRepository->findAll();
        $alredySubscribed = $this->ssRepository->getSubscriptionIdsOfSubscriber($subscriber->getSubscriberId());
        $availableSubscriptions = [];

        if (!empty($alredySubscribed)) {
            foreach($allSubscriptions as $subscription) {
                if(!in_array($subscription->getId(), $alredySubscribed)) {
                    $availableSubscriptions[] = $subscription;
                }
            }

            return $availableSubscriptions;
        }

        return $allSubscriptions;
    }

    public function getCurrentSubscription(Chat $chat, $code): array|null
    {
        $subscription = $this->subscriptionRepository->findOneBy(['code' => str_replace(Server::GET_THIS_COMMAND, '/', $code)]);
        $ss = $this->ssRepository->findOneBy(['subscriber_id' => $chat->getChatId(), 'subscription_id' => $subscription->getId()]);

        if (empty($ss) || empty($subscription)) {
            return null;
        }

        return [$subscription, $ss];
    }

    public function getSubscriptionsOfCurrentChat(Chat $chat)
    {
        return $this->ssRepository->getSubscriptionsOfSubscriber($chat->getChatId());
    }

    public function subscribeTo(Chat $chat, string $command): string
    {
        $subscriber = $this->subscriberRepository->findOrCreateSubscriber($chat->getChatId());
        $subscription = $this->subscriptionRepository->findOneBy(['code' => $command]);
        $alredySubscribed = $this->ssRepository->getSubscriptionIdsOfSubscriber($subscriber->getSubscriberId());

        if (!$subscription) {
            return 'К сожалению, не удалось подписаться';
        }

        if (in_array($subscription->getId(), $alredySubscribed)) {
            return 'На это вы уже подписаны';
        }

        $this->ssRepository->saveNewRecord($subscriber, $subscription);

        return 'Отлично! Подписка добавлена.' . PHP_EOL . $subscription->getName() . " будет отправляться вам каждое утро в районе 07:00";
    }

    public function getListOfSubscriptions(Chat $chat): array
    {     
        return $this->subscriptionRepository->getSubscriptionsNames(
            $this->ssRepository->getSubscriptionIdsOfSubscriber($chat->getChatId())
        );
    }

    public function getSubscriptionCount(Subscription $subscription)
    {
        $category = $this->getSubscriptionCategory($subscription);
        $contentService = $this->serviceFabric->getContentService($category)->setParameters($subscription);

        return $contentService->getCount();
    }

    public function removeSubscriptions(Chat $chat)
    {
        $ss = $this->ssRepository->findBy(['subscriber_id' => $chat->getChatId()]);

        foreach($ss as $item) {
            $this->ssRepository->delete($item);
        }
    }

    public function removeSubscription(Chat $chat, Subscription $subscription): bool
    {
        $ss = $this->ssRepository->findOneBy(['subscriber_id' => $chat->getChatId(), 'subscription_id' => $subscription->getId()]);

        if ($ss instanceof SubscriberSubscription) {
            $this->ssRepository->delete($ss);
            return true;
        }

        return false;
    }

    public function publishSketches($array, $sketchName, $code) 
    {
        $this->subscriptionRepository->create($sketchName, '/' . $code);
        $this->skecthService->publishSketchesToDb($array, $code);
    }

    private function prepareDataForCommonSend(array $ss): array
    {
        $subscribers = array_unique(array_map(fn($ss) => $ss->getSubscriberId(), $ss));
        $preparedData = [];

        foreach($subscribers as $subscriber) {
            $preparedData[$subscriber] = array_values(array_filter($ss, fn($ss) => $ss->getSubscriberId() === $subscriber));
        }

        return $preparedData;
    }

    private function getSubscriptionCategory(Subscription $subscription)
    {
        // ХАК! КОСТЫЛЬ! КОШМАР!
        return match ($subscription->getCode()) {
            '/girl' => 'poll',
            default => 'video',
        };
    }

}