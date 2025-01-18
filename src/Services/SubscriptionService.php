<?php

namespace App\Services;

use App\Api\TelegramApi\TelegramApi;
use App\Buttons\Button;
use App\Buttons\ButtonService;
use App\Entity\Subscriber;
use App\Entity\Chat;
use App\Entity\SubscriberSubscription;
use App\Entity\Subscription;
use App\TelegramBot\TelegramBot;
use App\HttpApiAdapters\GuzzleHttpAdapter;
use App\Repository\SubscriberRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\SubscriberSubscriptionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Server\Server;
use Exception;

class SubscriptionService {
    private $bot;

    const DONES_CHAT_ID = -1001993053984;
    const TEST_CHAT_ID = -1002337503652;

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
            $girls = $this->girlService->getGirlsInfo(3, $saveChanges);
            $this->sendGirlsPoll($chatId, $girls);

            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
            $this->logger->error($e->getMessage());
            $this->logErrorMessageToTg($e);
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

    public function sendHello($id, $message)
    {
        $this->bot->api->sendMessage($id, $message);
    }

    public function sendContentToSubscribers()
    {
        $allSubscriberSubscription = $this->ssRepository->findBy(['subscriber_id' => [SubscriptionService::TEST_CHAT_ID, 788788415]]);

        if (empty($allSubscriberSubscription)) {
            return;
        }

        $allSubscriberSubscription = $this->prepareDataForCommonSend($allSubscriberSubscription);

        foreach($allSubscriberSubscription as $subscriberId => $subscriptions) {
            $this->sendHello($subscriberId, 'Привет! :)');
            foreach($subscriptions as $ss) {
                $subscription = $this->subscriptionRepository->findOneBy(['id' => $ss->getSubscriptionId()]);
                $this->sendSubscription($subscription, $ss, true);
            }
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

    public function sendSubscription(Subscription $subscription, SubscriberSubscription $ss, $needSleep = false)
    {
        $number = $ss->getLastWatchedSeries() + 1;
        $link = $this->getSubscriptionLink($subscription->getCode(), $number);

        if (!$link) {
            $this->bot->api->sendMessage(
                $ss->getSubscriberId(), 
                "Я прислал вам все серии " . $subscription->getName() . "." . PHP_EOL . 'Вы можете попробовать подписаться на что-то еще. Для этого отправьте /start'
            );
            $this->ssRepository->delete($ss);
            return;
        }

        $this->ssRepository->save($ss->setLastWatchedSeries($number));

        $this->bot->api->sendMessage($ss->getSubscriberId(), $link);

        if ($needSleep) {
            sleep(10);
        }
    }

    public function sendCurrent(Subscription $subscription, SubscriberSubscription $ss, int $number)
    {
        $link = $this->getSubscriptionLink($subscription->getCode(), $number);
        if (!$link) {
            $this->bot->api->sendMessage(
                $ss->getSubscriberId(),
                "Ой, что-то пошло нет так"
            );
        }

        $this->bot->api->sendMessage($ss->getSubscriberId(), $link, ['reply_markup' => ButtonService::getInlineKeyboardForCurrentSeries()]);
    }

    public function sendNext(Subscription $subscription, SubscriberSubscription $ss)
    {
        $number = $ss->getLastWatchedSeries() + 1;
        $link = $this->getSubscriptionLink($subscription->getCode(), $number);

        if (!$link) {
            $this->bot->api->sendMessage(
                $ss->getSubscriberId(), 
                "Я прислал вам все серии " . $subscription->getName() . "." . PHP_EOL . 'Вы можете попробовать подписаться на что-то еще. Для этого отправьте /start'
            );
            $this->ssRepository->delete($ss);
            return;
        }

        $this->ssRepository->save($ss->setLastWatchedSeries($number));

        $this->bot->api->sendMessage($ss->getSubscriberId(), $link, ['reply_markup' => ButtonService::getInlineKeyboardForNextSeries()]);
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
        $sketchName = str_replace('/', '', $subscription->getCode());

        if (!is_string($sketchName)) {
            return;
        }

        $sketches = $this->skecthService->getAllSketchesByName($sketchName);

        return count($sketches);
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


    private function getSubscriptionLink($name, $number)
    {
        return $this->skecthService->getSketchLink(
            $this->getSubscriptionNameByCode($name), 
            $number
        );
    }

    private function getSubscriptionNameByCode(string $code): string
    {
        return str_replace('/', '', $code);
    }

    public function publishSketches($array, $sketchName, $code) 
    {
        $this->subscriptionRepository->create($sketchName, '/' . $code);
        $this->skecthService->publishSketchesToDb($array, $code);
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

    private function prepareDataForCommonSend(array $ss): array
    {
        $subscribers = array_unique(array_map(fn($ss) => $ss->getSubscriberId(), $ss));
        $preparedData = [];

        foreach($subscribers as $subscriber) {
            $preparedData[$subscriber] = array_values(array_filter($ss, fn($ss) => $ss->getSubscriberId() === $subscriber));
        }

        return $preparedData;
    }

}