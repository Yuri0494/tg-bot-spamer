<?php

namespace App\Services;

use App\Api\TelegramApi\TelegramApi;
use App\Entity\Subscriber;
use App\Entity\Chat;
use App\Entity\Subscription;
use App\TelegramBot\TelegramBot;
use App\HttpApiAdapters\GuzzleHttpAdapter;
use App\Repository\SubscriberRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\SubscriberSubscriptionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Exception;

class SubscriptionService {
    private $bot;

    const DONES_CHAT_ID = -1001993053984;
    const TEST_CHAT_ID = -1002337503652;
    const SHARKS_CHAT_ID =  -696758173;

    public function __construct(
        private SketchService $skecthService,
        private GirlService $girlService,
        private SubscriberRepository $subscriberRepository,
        private SubscriptionRepository $subscriptionRepository,
        private SubscriberSubscriptionRepository $ssRepository,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
        // TO DO Токен бота должен передаваться в параметрах к сервису
        $this->bot = new TelegramBot(
            new TelegramApi((new GuzzleHttpAdapter('https://api.telegram.org/bot6768896921:AAHSiWv6mmLSdd6b7kLVOIy9XXKltN8KIlg/')))
        );
    }

    public function sendMessagesToDonesChat()
    {
        $chatId = SubscriptionService::DONES_CHAT_ID;
        $saveChanges = true;

        $this->em->beginTransaction();
        try {
            // $this->bot->api->sendMessage($chatId, 'Доброе утро, благородные доны!');

            // $sketches = [
            //     // 'dulin' => $this->skecthService->getNextSketchLink('dulin', $saveChanges),
            //     'taganrog' => $this->skecthService->getNextSketchLink('taganrog', $saveChanges),
            //     'migrant' => $this->skecthService->getNextSketchLink('migrant', $saveChanges),
            // ];

            // $this->sendSketches($sketches, $chatId);

            $girls = $this->girlService->getGirlsInfo(3, $saveChanges);
            $this->sendGirlsPoll($chatId, $girls);

            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
            $this->logger->error($e->getMessage());
            $this->logErrorMessageToTg($e);
        }
    }

    public function sendMessagesToSharks()
    {
        $this->em->beginTransaction();
        try {
            $this->bot->api->sendMessage(SubscriptionService::SHARKS_CHAT_ID, 'Доброе утро, насяльника!');

            $sketches = [
                'migrant' => $this->skecthService->getSketchLink('migrant', 55),
            ];   

            $this->sendSketches($sketches, SubscriptionService::SHARKS_CHAT_ID);

            $this->em->commit();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logErrorMessageToTg($e);
            $this->em->rollback();
        }
    }

    protected function sendSketches($sketches, $chatId)
    {
        foreach($sketches as $name => $link) {

            if(!$link) {
                $this->bot->api->sendMessage($chatId, 'К сожалению, видео не найдено' . '(' . $name. ')');
            }

            $this->bot->api->sendMessage($chatId, $link);
        }
    }

    public function sendGirlsPoll($chatId, array $girls, $sleepTime = 30)
    {
        foreach($girls as $girl) {
            $mediaGroup = [];
            foreach($girl['img_links'] as $link) {
                $mediaGroup[] = [
                    'type' => 'photo',
                    'media' => $link,
                ];
            }

            if ($girl['personal_info']) {
                $this->bot->api->sendMessage($chatId, $girl['personal_info']);
            }

            $this->bot->api->sendMediaGroup($chatId, json_encode($mediaGroup));
            $this->bot->api->sendPoll($chatId, 'Как вам девочка?', $this->getStandartPoll());
            sleep($sleepTime);
        }
    }

    public function getSubscriptions(Chat $chat)
    {
        $subscriber = $this->subscriberRepository->findOrCreateSubscriber($chat->getChatId());
        $subscriptions = $this->subscriptionRepository->findAll();
        $alredySubscribed = $this->ssRepository->getSubscriptionIdsOfSubscriber($subscriber->getSubscriberId());
        $filteredSubscriptions = [];

        if(!empty($alredySubscribed)) {
            foreach($subscriptions as $subscription) {
                if(!in_array($subscription->getId(), $alredySubscribed)) {
                    $filteredSubscriptions[] = $subscription;
                }
            }

            return $filteredSubscriptions;
        }

        return $subscriptions;
    }

    public function getSubscriptionsOfCurrentChat(Chat $chat)
    {
        $subscriber = $this->subscriberRepository->findOrCreateSubscriber($chat->getChatId());
        $subscriptions = $this->subscriptionRepository->findAll();
        $alredySubscribed = $this->ssRepository->getSubscriptionIdsOfSubscriber($subscriber->getSubscriberId());
        $filteredSubscriptions = [];

        if(empty($alredySubscribed)) {
            return [];
        }

        foreach($subscriptions as $subscription) {
            if(in_array($subscription->getId(), $alredySubscribed)) {
                $filteredSubscriptions[] = $subscription;
            }
        }

        return $filteredSubscriptions;
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
        $subscriber = $this->subscriberRepository->findOrCreateSubscriber($chat->getChatId());
        $alredySubscribed = $this->ssRepository->getSubscriptionIdsOfSubscriber($subscriber->getSubscriberId());
        $subscriptionNames = $this->subscriptionRepository->getSubscriptionsNames($alredySubscribed);
       
        return $subscriptionNames;
    }

    public function sendContentToSubscribers()
    {
        $ss = $this->ssRepository->findAll();

        if (empty($ss)) {
            return;
        }

        foreach($ss as $item) {
            $subscription = $this->subscriptionRepository->findOneBy(['id' => $item->getSubscriptionId()]);
            $sketchName = str_replace('/', '', $subscription->getCode());

            if (!is_string($sketchName)) {
                continue;
            }

            $currentSeries = $item->getLastWatchedSeries() + 1;
            $link = $this->skecthService->getSketchLink($sketchName, $currentSeries);

            if (!$link) {
                $this->bot->api->sendMessage(
                    $item->getSubscriberId(), 
                    "Я прислал вам все серии " . $subscription->getName() . "." . PHP_EOL . 'Вы можете попробовать подписаться на что-то еще. Для этого отправьте /start'
                );
                $this->ssRepository->delete($item);
                continue;
            }

            $this->ssRepository->save($item->setLastWatchedSeries($currentSeries));

            $this->bot->api->sendMessage($item->getSubscriberId(), $link);
            sleep(10);
        }
    }

    public function sendHello()
    {
        $subscriberIds = $this->ssRepository->getActiveSubscribersIds();

        foreach($subscriberIds as $id) {
            $this->bot->api->sendMessage($id, 'Привет! :)');
        }
    }

    public function removeSubscriptions(Chat $chat)
    {
        $ss = $this->ssRepository->findBy(['subscriber_id' => $chat->getChatId()]);

        foreach($ss as $item) {
            $this->ssRepository->delete($item);
        }
    }

    public function publishSketches($array, $sketchName, $code) 
    {
        $this->subscriptionRepository->create($sketchName, '/' . $code);
        $this->skecthService->publishSketchesToDb($array, $code);
    }

    public function getCurrentSubscription(Chat $chat, $code)
    {
        $ss = $this->ssRepository->findOneBy(['subscriber_id' => $chat->getChatId()]);
        $subscription = $this->subscriptionRepository->findOneBy(['code' => str_replace('/see-', '/', $code)]);

        if (empty($ss) || empty($subscription)) {
            return;
        }

        $sketchName = str_replace('/', '', $subscription->getCode());

        if (!is_string($sketchName)) {
            return;
        }

        $currentSeries = $ss->getLastWatchedSeries() + 1;
        $link = $this->skecthService->getSketchLink($sketchName, $currentSeries);

        if (!$link) {
            $this->bot->api->sendMessage(
                $chat->getChatId(), 
                "Я прислал вам все серии " . $subscription->getName() . "." . PHP_EOL . 'Вы можете попробовать подписаться на что-то еще. Для этого отправьте /start'
            );
            $this->ssRepository->delete($ss);
            return;
        }

        $this->ssRepository->save($ss->setLastWatchedSeries($currentSeries));

        $this->bot->api->sendMessage($ss->getSubscriberId(), $link);
    }


    public function getStandartPoll()
    {
        return json_encode([                
            [
                'text' => 1,
            ],
            [
                'text' => 2,
            ],
            [
                'text' => 3,
            ],
            [
                'text' => 4,
            ],
            [
                'text' => 5,
            ],
            [
                'text' => 6,
            ],
            [
                'text' => 7,
            ],
            [
                'text' => 8,
            ],
            [
                'text' => 9,
            ],
            [
                'text' => 10,
            ],
        ]);
    }

    private function logErrorMessageToTg(Exception $e)
    {
        $this->bot->api->sendMessage(SubscriptionService::TEST_CHAT_ID, $e->getMessage());
    }

}