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
    private $tgBot;
    private $request;
    private $type;
    private User $user;
    private Chat $chat;
    
    public function __construct(
        private ChatRepository $chatRepository,
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
        if ($this->request['text'] === '/start') {
            $this->chat = $this->chatRepository->createOrFind($this->request['chat']);

            if($this->isBlockedChanels()) {
                return;
            }

            $this->getAvailableSubscriptions();
        }
    }

    private function handleCallbackRequest()
    {
        $this->chat = $this->chatRepository->createOrFind($this->request['message']['chat']);

        if ($this->isBlockedChanels()) {
                return;
        }

        $command = $this->request['data'];

        if ($command === '/start') {
            $this->getAvailableSubscriptions();
        } elseif('/see' === $command) {
            $this->sendContentForSee();
        } elseif(str_contains($command, '/see-')) {
            $this->subscriptionService->getCurrentSubscription($this->chat, $command);
        } else {
            $this->subscribeTo($command);
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

    private function getAvailableSubscriptions()
    {
        $availableSubscriptions = $this->subscriptionService->getSubscriptions($this->chat);
        $text = 'На что вы хотите подписаться?';

        if (!$availableSubscriptions) {
            $namesOfSubscriptions = $this->subscriptionService->getListOfSubscriptions($this->chat);
            $text = 'На данный момент нет доступных подписок' .  PHP_EOL . 'Ваши подписки: ' . PHP_EOL . implode(PHP_EOL, $namesOfSubscriptions);
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

    private function sendContentForSee()
    {
        $availableSubscriptions = $this->subscriptionService->getSubscriptionsOfCurrentChat($this->chat);
        $text = empty($availableSubscriptions) ? 'Сейчас у вас нет подписок' : 'Что вы хотите посмотреть?';

        $this->tgBot->api->sendMessage(
            $this->chat->getChatId(), 
            $text, 
            ['reply_markup' => ButtonService::getInlineKeyboardForSee($availableSubscriptions)]
        );
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
}