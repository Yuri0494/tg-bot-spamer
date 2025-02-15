<?php

namespace App\TelegramBotRequest;

use App\Entity\User;
use App\Entity\Chat;
use App\Repository\UserRepository;
use App\Repository\ChatRepository;
use Exception;

class TelegramBotRequest {
    public User $user;
    public Chat $chat;
    public string $type;
    public string $command;
    public $messageId;
    private $request;

    public function __construct(
        private ChatRepository $chatRepository,
        private UserRepository $userRepository,
    )
    {
        $this->request = json_decode(file_get_contents('php://input') ?? [], true);
        $this->type = $this->getTypeOfRequest($this->request);

        if ($this->type === 'not_handled') {
            return;
        }
        
        $this->user = $this->initUser();
        $this->chat = $this->initChat();
    }

    public function getRequestData()
    {
        return $this->request[$this->type];
    }

    public function getCommand()
    {
        return $this->request[$this->type]['text'] ?? $this->request[$this->type]['data'];
    }

    public function getMessageId()
    {
        if ($this->type !== 'callback_query') {
            throw new Exception('Сейчас метод недоступен');
        }
        
        return $this->request[$this->type]['message']['message_id'];
    }

    public function isBlockedRequest(): bool
    {
        $chatId = $this->chat->getChatId();
        $userId = $this->user->getTgId();

        if (in_array($chatId, [-1001993053984, -1002337503652, -696758173, -1002490200919]) && !in_array($userId, [788788415])) {
            return true;
        }

        return false;
    }

    private function getTypeOfRequest(): string
    {
        $isMessage = $this->request['message'] ?? false;
        $isCallback = $this->request['callback_query'] ?? false;
        $isMyChatMember = $this->request['my_chat_member'] ?? false;
        $isPollAnswer = $this->request['poll_answer'] ?? false;
        
        if ($isMessage) {
            return 'message';
        } elseif($isCallback) {
            return 'callback_query';
        } elseif($isMyChatMember) {
            return 'my_chat_member';
        }

        return 'not_handled';
    }

    private function initUser()
    {
        return match($this->type) {
            'message' => $this->userRepository->createOrFind($this->request[$this->type]['from']),
            'callback_query', 'my_chat_member' => $this->userRepository->createOrFind($this->request[$this->type]['from']),
        };
    }

    private function initChat()
    {
        return match($this->type) {
            'message', 'my_chat_member' => $this->chatRepository->createOrFind($this->request[$this->type]['chat']),
            'callback_query' => $this->chatRepository->createOrFind($this->request[$this->type]['message']['chat']),
        };
    }
}