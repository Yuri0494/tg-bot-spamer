<?php

namespace App\Server;

use Exception;
use GuzzleHttp\Client;
use App\TelegramBot\TelegramBot;
use App\Api\TelegramApi\TelegramApi;
use App\HttpApiAdapters\GuzzleHttpAdapter;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Repository\ChatRepository;
use App\Entity\Chat;
use App\Buttons\ButtonService;
use App\Services\SubscriptionService;

class Server {
    const START_COMMAND = '/start';
    const GET_COMMAND = '/get';
    const GET_THIS_COMMAND = '/get-';
    const GET_NEXT = '/get-next';
    const UNSUBSCRIBE = '/unsubscribe';

    private $tgBot;
    private $request;
    private $type;
    private User $user;
    private Chat $chat;
    
    public function __construct(
        private ChatRepository $chatRepository,
        private UserRepository $userRepository,
        private SubscriptionService $subscriptionService
    )
    {
        $this->tgBot = new TelegramBot(
            new TelegramApi((new GuzzleHttpAdapter('https://api.telegram.org/bot6768896921:AAHSiWv6mmLSdd6b7kLVOIy9XXKltN8KIlg/')))
        );
    }

    public function handleRequest()
    {
        try {
            $request = json_decode(file_get_contents('php://input') ?? [], true);
            $this->type = $this->getTypeOfRequest($request);

            if ($this->type === 'not handled') {
                return;
            }

            $this->request = $request[$this->type];

            if ($this->type === 'message' && array_key_exists('text', $this->request)) {
                $this->handleMessageRequest();
            } elseif($this->type === 'callback_query') {
                $this->handleCallbackRequest();
            } elseif($this->type === 'my_chat_member') {
                $this->handleMyChatMember();
            }


        } catch (Exception $e) {
            $this->tgBot->api->sendMessage(788788415, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    private function handleMessageRequest()
    {
        $this->user = $this->userRepository->createOrFind($this->request['from']);
        $this->chat = $this->chatRepository->createOrFind($this->request['chat']);
        $commandInMessage = $this->request['text'];

        if ($commandInMessage === Server::START_COMMAND) {

            if($this->isBlockedChanels()) {
                return;
            }

            $this->getAvailableSubscriptionsForSubscribe();
        }

        if (
            str_contains($this->user->getLastCommand(), Server::GET_THIS_COMMAND) &&
            is_int((int) $this->request['text'])
        ) {
            $this->tryToSendSubscription($commandInMessage);
        }
    }

    private function handleCallbackRequest()
    {
        $this->chat = $this->chatRepository->createOrFind($this->request['message']['chat']);
        $this->user = $this->userRepository->createOrFind($this->request['from']);

        if ($this->isBlockedChanels()) {
            return;
        }

        $command = $this->request['data'];

        if (isset($this->request['message']['message_id'])) {
            $this->tgBot->api->sendDeleteMessage($this->chat->getChatId(), $this->request['message']['message_id']);
        }
        
        switch ($command) {
            case Server::START_COMMAND:
                $this->setLastCommandOfUser('');
                $this->getAvailableSubscriptionsForSubscribe();
                break;
            case Server::GET_COMMAND:
                $this->setLastCommandOfUser('');
                $this->getSubscriptionsForView();
                break;
            case Server::GET_NEXT:
                $this->tryToSendNextSeries();
                break;
            // Возможно следует переименовать команду на view
            case str_contains($command, Server::GET_THIS_COMMAND):
                $this->setLastCommandOfUser($command);
                $this->getCurrentSubscriptionForView($command);
                break;
            case Server::UNSUBSCRIBE:
                $this->unsubscribe();
                break;
            default:
                $this->subscribeTo($command);
        }
    }

    private function getAvailableSubscriptionsForSubscribe()
    {
        $availableSubscriptions = $this->subscriptionService->getAvailableSubscriptions($this->chat);
        $text = 'На что вы хотите подписаться?';

        if (!$availableSubscriptions) {
            $namesOfSubscriptions = $this->subscriptionService->getListOfSubscriptions($this->chat);
            $text = 'На данный момент нет доступных подписок' .  
                    PHP_EOL . 
                    'Ваши подписки: ' . 
                    PHP_EOL . 
                    implode(PHP_EOL, $namesOfSubscriptions);
        }

        $this->tgBot->api->sendMessage(
            $this->chat->getChatId(), 
            $text, 
            ['reply_markup' => ButtonService::getInlineKeyboardForStart($availableSubscriptions)]
        );
    }

    private function subscribeTo($command)
    {
        $textAfterSubscribe = $this->subscriptionService->subscribeTo($this->chat, $command);
        $this->tgBot->api->sendMessage(
            $this->chat->getChatId(), 
            $textAfterSubscribe, 
            ['reply_markup' => ButtonService::getInlineKeyboardAfterSubscribe()]
        );
    }

    private function getSubscriptionsForView()
    {
        $subscriptions = $this->subscriptionService->getSubscriptionsOfCurrentChat($this->chat);
        $this->tgBot->api->sendMessage(
            $this->chat->getChatId(), 
            empty($subscriptions) ? 'Сейчас у вас нет подписок' : 'Что вы хотите посмотреть?', 
            ['reply_markup' => ButtonService::getInlineKeyboardForCurrentChat($subscriptions)]
        );
    }

    private function getCurrentSubscriptionForView($command)
    {
        try {
            [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->chat, $command);
            $lastSeries = $subscriptionSubscriber->getLastWatchedSeries();
            $count = $this->subscriptionService->getSubscriptionCount($subscription);

            $this->tgBot->api->sendMessage(
                $this->chat->getChatId(),
                "Всего доступно серий к просмотру: $count"
                . PHP_EOL .
                "Последняя просмотренная серия: $lastSeries"
                . PHP_EOL .
                "Для просмотра интересующей вас серии, отправьте ее номер в сообщении",
                ['reply_markup' => ButtonService::getInlineKeyboardForView()]
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function tryToSendNextSeries()
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->chat, $this->user->getLastCommand());
        $this->subscriptionService->sendNext($subscription, $subscriptionSubscriber);
    }

    private function tryToSendSubscription($command)
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->chat, $this->user->getLastCommand());
        $count = $this->subscriptionService->getSubscriptionCount($subscription);

        if ($command > $count || $command < 1) {
            $this->tgBot->api->sendMessage(
                $this->chat->getChatId(),
                "Попробуйте ввести другое число" . PHP_EOL .
                "Напоминаем, что всего доступно серий к просмотру: $count",
                ['reply_markup' => ButtonService::getInlineKeyboardForView()]
            );
            return;
        }
        $this->subscriptionService->sendCurrent($subscription, $subscriptionSubscriber, $command);
    }

    private function unsubscribe()
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->chat, $this->user->getLastCommand());
        $this->setLastCommandOfUser('');

        if ($this->subscriptionService->removeSubscription($this->chat, $subscription)) {
            $this->tgBot->api->sendMessage(
                $this->chat->getChatId(),
                "Ок! Подписка удалена",
                ['reply_markup' => ButtonService::getInlineKeyboardAfterUnsubscribe()]
            );
        } else {
            $this->tgBot->api->sendMessage(
                $this->chat->getChatId(),
                "Ой, что-то пошло не так :(",
                ['reply_markup' => ButtonService::getInlineKeyboardAfterUnsubscribe()]
            );
        }
    }

    private function getTypeOfRequest(array $request): string
    {
        $isMessage = $request['message'] ?? false;
        $isCallback = $request['callback_query'] ?? false;
        $isMyChatMember = $request['my_chat_member'] ?? false;
        
        if ($isMessage) {
            return 'message';
        } elseif($isCallback) {
            return 'callback_query';
        } elseif($isMyChatMember) {
            return 'my_chat_member';
        }

        return 'not handled';
    }

    private function isBlockedChanels()
    {
        $chatId = $this->chat->getChatId();
        $userId = $this->request['from']['id'];
        if(in_array($chatId, [-1001993053984, -1002337503652, -696758173]) && !in_array($userId, [788788415])) {
            $this->tgBot->api->sendMessage($chatId, "К сожалению, вам нельзя настраивать меня в данном чате :((");
            return true;
        }
    }

    private function handleMyChatMember()
    {
        $status = $this->request['new_chat_member']['status'] ?? ''; 

        if (in_array($status, ['left', 'kicked'])) {
            $this->chat = $this->chatRepository->createOrFind($this->request['chat']);
            $this->subscriptionService->removeSubscriptions($this->chat);
        }
    }

    private function setLastCommandOfUser($command)
    {
        $this->user->setLastCommand($command);
        $this->userRepository->save($this->user);
    }
}