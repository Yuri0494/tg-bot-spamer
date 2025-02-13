<?php

namespace App\Server;

use Exception;
use App\TelegramBot\TelegramBot;
use App\Repository\UserRepository;
use App\Repository\ChatRepository;
use App\Buttons\ButtonService;
use App\Services\SubscriptionService;
use App\TelegramBotRequest\TelegramBotRequest;

class Server {
    const START_COMMAND = '/start';
    const GET_COMMAND = '/view';
    const GET_THIS_COMMAND = '/view-'; // Подписка и ее настройки
    const GET_NEXT = '/view-next';
    const GET_PREV = '/view-prev';
    const SET_SERIES = '/set-series';
    const UNSUBSCRIBE = '/unsubscribe';
  
    public function __construct(
        private ChatRepository $chatRepository,
        private UserRepository $userRepository,
        private SubscriptionService $subscriptionService,
        private TelegramBot $tgBot,
        private TelegramBotRequest $req,
    )
    {}

    public function handleRequest()
    {
        try {
            // $this->tgBot->api->sendMessage('-696758173', 'Спасиба, Денисс Леонидович');
            switch($this->req->type) {
                case 'not handled':
                    return;
                case 'message' && array_key_exists('text', $this->req->getRequestData()):
                    $this->handleMessageRequest();
                    return;
                case 'callback_query':
                    $this->handleCallbackRequest();
                    return;
                case 'my_chat_member':
                    $this->handleMyChatMember();
                    return;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Обработка текстового сообщения
    private function handleMessageRequest()
    {
        try {
            if ($this->req->isBlockedRequest()) {
                return $this->actionBlock();
            }
    
            // Старт приложения
            if ($this->req->getCommand() === Server::START_COMMAND) {
                return $this->actionStart();
            }
            // Роут для получения конкретной серии
            if (
                str_contains($this->req->user->getCurrentCommand(), Server::GET_THIS_COMMAND) &&
                is_int((int) $this->req->getCommand())
            ) {
                return $this->tryToSendSubscription($this->req->getCommand());
            }
            // Роут для получения конкретной серии
            if (
                str_contains($this->req->user->getCurrentCommand(), Server::SET_SERIES) &&
                is_int((int) $this->req->getCommand())
            ) {
                return $this->tryToSetSeries();
            }
        } catch(Exception $e) {
            $this->tgBot->api->sendMessage(788788415, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    // Обработка коллбэк-сообщения (пользователь нажал на кнопку)
    private function handleCallbackRequest()
    {
        try {
            if ($this->req->isBlockedRequest()) {
                return $this->actionBlock();
            }
    
            // Удаляем предыдущее сообщение пользователя
            if ($this->req->getMessageId()) {
                $this->tgBot->api->sendDeleteMessage($this->req->chat->getChatId(), $this->req->getMessageId());
            }
    
            $command = $this->req->getCommand();
            
            switch ($command) {
                case Server::START_COMMAND:
                    return $this->actionStart();
                case Server::GET_COMMAND:
                    return $this->actionView();
                case Server::GET_NEXT:
                    return $this->actionViewNext();
                case Server::GET_PREV:
                    return $this->actionViewPrev();
                case Server::SET_SERIES:
                    return $this->actionSetSeries();
                case str_contains($command, Server::GET_THIS_COMMAND):
                    return $this->actionViewCurrent($command);
                case Server::UNSUBSCRIBE:
                    return $this->unsubscribe();
                default:
                    return $this->subscribe($command);
            }
        } catch (Exception $e) {
            $this->tgBot->api->sendMessage(788788415, $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }
    }

    private function actionStart()
    {
        $this->setCurrentCommandOfUser('');
        $availableSubscriptions = $this->subscriptionService->getAvailableSubscriptions($this->req->chat);
        $text = 'На что вы хотите подписаться?';

        if (!$availableSubscriptions) {
            $namesOfSubscriptions = $this->subscriptionService->getListOfSubscriptions($this->req->chat);
            $text = 'На данный момент нет доступных подписок' .  
                    PHP_EOL . 
                    'Ваши подписки: ' . 
                    PHP_EOL . 
                    implode(PHP_EOL, $namesOfSubscriptions);
        }

        $this->tgBot->api->sendMessage(
            $this->req->chat->getChatId(), 
            $text, 
            ['reply_markup' => ButtonService::getInlineKeyboardForStart($availableSubscriptions)]
        );
    }

    private function actionView()
    {
        $this->setCurrentCommandOfUser('');
        $subscriptions = $this->subscriptionService->getSubscriptionsOfCurrentChat($this->req->chat);
        $this->tgBot->api->sendMessage(
            $this->req->chat->getChatId(), 
            empty($subscriptions) ? 'Сейчас у вас нет подписок' : 'Что вы хотите посмотреть?', 
            ['reply_markup' => ButtonService::getInlineKeyboardForCurrentChat($subscriptions)]
        );
    }

    private function actionViewCurrent($command)
    {
        $this->setCurrentCommandOfUser($command);
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->req->chat, $command);
        $count = $this->subscriptionService->getSubscriptionCount($subscription);
        $currentSeries = $subscriptionSubscriber->getCurrentSeriesForWatching();

        if ($count < $currentSeries) {
            $currentSeries = $currentSeries . " (кажется, вы уже все посмотрели)";
        }

        $this->tgBot->api->sendMessage(
            $this->req->chat->getChatId(),
            "Всего доступно: $count"
            . PHP_EOL .
            "Текущая: $currentSeries"
            . PHP_EOL .
            "Для просмотра интересующего вас контента, отправьте его номер в сообщении или нажмите 'смотреть далее'",
            ['reply_markup' => ButtonService::getInlineKeyboardForView()]
        );
    }

    private function actionSetSeries()
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->req->chat, $this->req->user->getCurrentCommand());
        $count = $this->subscriptionService->getSubscriptionCount($subscription);
        $this->setPrevCommandOfUser($this->req->user->getCurrentCommand());
        $this->setCurrentCommandOfUser($this->req->getCommand());

        $this->tgBot->api->sendMessage(
            $this->req->chat->getChatId(),
            "Всего доступно к просмотру: $count"
            . PHP_EOL .
            "Откуда хотите продолжить просмотр?",
            ['reply_markup' => ButtonService::getInlineKeyboardForSetCommand($this->req->user->getPrevCommand())]
        );
    }

    private function actionViewNext()
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->req->chat, $this->req->user->getCurrentCommand());
        $this->subscriptionService->sendNext($subscription, $subscriptionSubscriber);
    }

    private function actionViewPrev()
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->req->chat, $this->req->user->getCurrentCommand());
        $this->subscriptionService->sendPrevSubscription($subscription, $subscriptionSubscriber);
    }

    private function tryToSendSubscription($command)
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->req->chat, $this->req->user->getCurrentCommand());
        $this->subscriptionService->sendCurrent($subscription, $subscriptionSubscriber, $command);
    }

    private function tryToSetSeries()
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->req->chat, $this->req->user->getPrevCommand());
        $count = $this->subscriptionService->getSubscriptionCount($subscription);
        $series = (int) $this->req->getCommand();

        if ($series > $count || $series <= 0) {
            $this->tgBot->api->sendMessage(
                $this->req->chat->getChatId(),
                "Введенные вами данные некорректны. Напоминаю, к просмотру доступно: $count",
                ['reply_markup' => ButtonService::getInlineKeyboardForSetCommand($this->req->user->getPrevCommand())]
            );
            return;
        }

        if ($this->subscriptionService->setLastWatchedSeries($subscriptionSubscriber, $series - 1)) {
            $this->tgBot->api->sendMessage(
                $this->req->chat->getChatId(),
                "Ок! Теперь просмотр будет продолжен с указанной серии",
                ['reply_markup' => ButtonService::getInlineKeyboardForSetCommand($this->req->user->getPrevCommand())]
            );
        } else {
            $this->tgBot->api->sendMessage(
                $this->req->chat->getChatId(),
                "К сожалению, что-то пошло не так. Попробуйте отправить номер серии еще раз.",
                ['reply_markup' => ButtonService::getInlineKeyboardForSetCommand($this->req->user->getPrevCommand())]
            );
        }
    }

    private function unsubscribe()
    {
        [$subscription, $subscriptionSubscriber] = $this->subscriptionService->getCurrentSubscription($this->req->chat, $this->req->user->getCurrentCommand());
        $this->setCurrentCommandOfUser('');

        if ($this->subscriptionService->removeSubscription($this->req->chat, $subscription)) {
            $this->tgBot->api->sendMessage(
                $this->req->chat->getChatId(),
                "Ок! Подписка удалена",
                ['reply_markup' => ButtonService::getInlineKeyboardAfterUnsubscribe()]
            );
        } else {
            $this->tgBot->api->sendMessage(
                $this->req->chat->getChatId(),
                "Ой, что-то пошло не так :(",
                ['reply_markup' => ButtonService::getInlineKeyboardAfterUnsubscribe()]
            );
        }
    }

    private function subscribe($command)
    {
        $textAfterSubscribe = $this->subscriptionService->subscribeTo($this->req->chat, $command);
        $this->tgBot->api->sendMessage(
            $this->req->chat->getChatId(), 
            $textAfterSubscribe, 
            ['reply_markup' => ButtonService::getInlineKeyboardAfterSubscribe()]
        );
    }

    private function actionBlock()
    {
        return $this->tgBot->api->sendMessage($this->req->chat->getChatId(), "К сожалению, вам нельзя настраивать меня в данном чате :((");
    }

    private function handleMyChatMember()
    {
        try {
            $req = $this->req->getRequestData();
            $status = $req['new_chat_member']['status'] ?? ''; 
    
            if (in_array($status, ['left', 'kicked'])) {
                $this->req->chat = $this->chatRepository->createOrFind($req['chat']);
                $this->subscriptionService->removeSubscriptions($this->req->chat);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function setCurrentCommandOfUser($command)
    {
        $this->req->user->setCurrentCommand($command);
        $this->userRepository->save($this->req->user);
    }

    private function setPrevCommandOfUser($command)
    {
        $this->req->user->setPrevCommand($command);
        $this->userRepository->save($this->req->user);
    }

}