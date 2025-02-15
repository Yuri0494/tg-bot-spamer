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
    const CATEGORY_VIDEO = 'video';
    const CATEGORY_GIRLS = 'girl';

    public function __construct(
        private SketchService $skecthService,
        private GirlService $girlService,
        private SubscriberRepository $subscriberRepository,
        private SubscriptionRepository $subscriptionRepository,
        private SubscriberSubscriptionRepository $ssRepository,
        private EntityManagerInterface $em,
        private ContentServiceFabric $serviceFabric,
        private LoggerInterface $logger,
        private TelegramBot $tgBot,
    ) {}
    
    public function sendHello($id, $message)
    {
        $this->tgBot->api->sendMessage($id, $message);
    }

    public function sendContentToSubscribers()
    {
        $allSubscriberSubscription = $this->ssRepository->findAll();

        if (empty($allSubscriberSubscription)) {
            return;
        }

        $toSend = $this->prepareDataForCommonSend($allSubscriberSubscription);

        foreach($toSend as $subscriberId => $subscriptions) {
            try {
                $this->sendHello($subscriberId, 'Привет! :)');
                foreach($subscriptions as $ss) {
                    $subscription = $this->subscriptionRepository->findOneBy(['id' => $ss->getSubscriptionId()]);
                    $this->sendSubscription($subscription, $ss, true);
                }
            } catch (Exception $e) {
                $this->tgBot->api->logError($e);
            }
        }
    }

    public function sendSubscription(Subscription $subscription, SubscriberSubscription $ss, $needSleep = false)
    {
        $quantity = $subscription->getCategory() === 'girl' ? 3 : 1;
        $sleepTime = $subscription->getCategory() === 'girl' ? 30 : 1;
        $contentService = $this->serviceFabric->getContentService($subscription->getCategory())->setParameters($subscription, $quantity, $sleepTime);
        $number = $ss->getCurrentSeriesForWatching();
    
        $sended = $contentService->send($ss->getSubscriberId(), $number, false);
        $this->updateFieldOrDeleteSubscription($ss, $sended, $ss->getCurrentSeriesForWatching() + ($quantity - 1));

        if ($needSleep) {
            sleep(10);
        }
    }

    public function sendNext(Subscription $subscription, SubscriberSubscription $ss, $needSleep = false)
    {
        $quantity = 1;
        $contentService = $this->serviceFabric->getContentService($subscription->getCategory())->setParameters($subscription, $quantity);
        $number = $ss->getCurrentSeriesForWatching($quantity);
    
        $sended = $contentService->send($ss->getSubscriberId(), $number);  
        $this->updateFieldOrDeleteSubscription($ss, $sended, $number);

        if ($needSleep) {
            sleep(10);
        }
    }

    public function sendPrevSubscription(Subscription $subscription, SubscriberSubscription $ss)
    {
        $quantity = 1;
        $contentService = $this->serviceFabric->getContentService($subscription->getCategory())->setParameters($subscription, $quantity);
        $number = $ss->getPrevSeriesForWatching();
    
        $sended = $contentService->send($ss->getSubscriberId(), $number);  
        $this->updateFieldOrDeleteSubscription($ss, $sended, $number);
        sleep(2);
    }

    public function sendCurrent(Subscription $subscription, SubscriberSubscription $ss, int $number)
    {
        $quantity = 1;
        $contentService = $this->serviceFabric->getContentService($subscription->getCategory())->setParameters($subscription, $quantity);
        $contentService->send($ss->getSubscriberId(), $number);
    }

    public function getAvailableSubscriptions(Chat $chat)
    {
        try {
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
        } catch(\Throwable $e) {
            throw $e;
        }
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

        return 'Отлично! Подписка добавлена.' . 
        PHP_EOL . 
        $subscription->getName() . 
        " будет отправляться вам каждое утро в районе 07:00" . 
        PHP_EOL . 
        "Также в разделе 'Ваши подписки' вы можете управлять подпиской или посмотреть что-либо прямо сейчас."
        ;
    }

    public function getListOfSubscriptions(Chat $chat): array
    {     
        return $this->subscriptionRepository->getSubscriptionsNames(
            $this->ssRepository->getSubscriptionIdsOfSubscriber($chat->getChatId())
        );
    }

    public function getSubscriptionCount(Subscription $subscription)
    {
        $contentService = $this->serviceFabric->getContentService($subscription->getCategory())->setParameters($subscription);

        return $contentService->getCount();
    }

    public function setLastWatchedSeries(SubscriberSubscription $ss, int $series)
    {
        try {
            $this->ssRepository->save($ss->setLastWatchedSeries($series));
            return true;
        } catch(Exception $e) {
            throw $e;
        }
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
        $this->subscriptionRepository->create($sketchName, '/' . $code, self::CATEGORY_VIDEO);
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

    private function updateFieldOrDeleteSubscription(SubscriberSubscription $ss, bool $actual, int $lastWatchedSeries)
    {
        if (!$actual) {
           return $this->ssRepository->delete($ss);
        }
        
        $ss->setLastWatchedSeries($lastWatchedSeries);
        $this->ssRepository->save($ss);
    }

    public function getChatsInfo()
    {
       $ss = $this->ssRepository->findAll();
       $info = [];

       foreach($ss as $subscriber) {
            $info[] = $this->tgBot->api->getChat($subscriber->getSubscriberId());
       }

       return $info;
    }
}