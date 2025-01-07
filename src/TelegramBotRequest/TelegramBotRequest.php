<?php

namespace App\TelegramBotRequest;

class TelegramBotRequest {
    public $data;
    public bool $isCallback;
    public bool $isMessage;
    public bool $isMyChatMember;
    public string $typeOfRequest;
    public string | null $text;
    public string | null $command;
    public $chatMemberStatus;
    public $chatInstance;
    public $chatId;
    public $edited_message;
    public $from_id;

    public function __construct($request)
    {
        $this->data = $request;
        $this->isCallback = array_key_exists('callback_query', $this->data);
        $this->isMessage = array_key_exists('message', $this->data);
        $this->isMyChatMember = array_key_exists('my_chat_member', $this->data);
        $this->edited_message = array_key_exists('edited_message', $this->data);
        $this->typeOfRequest = $this->getTypeOfRequest();
        $this->text = $this->data[$this->typeOfRequest]['text'] ?? null;
        $this->command = $this->data[$this->typeOfRequest]['data'] ?? null;
        $this->chatMemberStatus = $this->data[$this->typeOfRequest]['new_chat_member']['status'] ?? null;
        $this->chatInstance = $this->data[$this->typeOfRequest]['chat_instance'] ?? null;
        $this->chatId = $this->data[$this->typeOfRequest]['chat']['id'] ?? null;
        $this->from_id = $this->data[$this->typeOfRequest]['from']['id'] ?? null;
    }

    public function getTypeOfRequest()
    {
        if($this->isCallback) {
            return $this->typeOfRequest = 'callback_query';
        } elseif($this->isMessage) {
            return $this->typeOfRequest = 'message';
        } elseif($this->isMyChatMember) {
            return $this->typeOfRequest = 'my_chat_member';
        } elseif($this->edited_message) {
            return $this->typeOfRequest = 'edited_message';
        }
    }

    public function getChatID() 
    {
        return match($this->typeOfRequest) {
            'callback_query' => $this->data['callback_query']['message']['chat']['id'],
            'message' => $this->data['message']['chat']['id'],
            'my_chat_member' => $this->data['my_chat_member']['chat']['id'],
            'edited_message' => $this->data['edited_message']['chat']['id'],
            default => null
        };
    }

}