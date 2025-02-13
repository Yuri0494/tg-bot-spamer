<?php

namespace App\TelegramBotRequest;

use App\Entity\User;
use App\Entity\Chat;
use App\Repository\UserRepository;
use App\Repository\ChatRepository;
use Exception;

class TelegramBotRequest {
    public string $type;
    private $request;
    public User $user;
    public Chat $chat;
    public string $command;
    public $messageId;

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

    private function getTypeOfRequest($request): string
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

    private function initChat()
    {
        switch ($this->type) {
            case 'message':
                return $this->chatRepository->createOrFind($this->request[$this->type]['chat']);
            case 'callback_query':
                return $this->chatRepository->createOrFind($this->request[$this->type]['message']['chat']);
        }
    }

    private function initUser()
    {
        switch ($this->type) {
            case 'message':
                return $this->userRepository->createOrFind($this->request[$this->type]['from']);
            case 'callback_query':
                return $this->userRepository->createOrFind($this->request[$this->type]['from']);
        }
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
}