<?php

namespace App\Api\TelegramApi;

use Exception;
use App\HttpApiAdapters\HttpAdapterInterface;

class TelegramApi {
    private HttpAdapterInterface $client;
    private int|string $chatIdForLogByDefault;

    public function __construct(HttpAdapterInterface $client, int|string $chatIdForLogByDefault)
    {
        $this->client = $client;
        $this->chatIdForLogByDefault = $chatIdForLogByDefault;
    }

    public function getMe()
    {
        try {
            return json_decode($this->client->sendGetRequest('getMe'), true);
        } catch (Exception $e) {
            return [];
        }
    }

    public function sendMessage($chatId, $text = null, $params = []) 
    {
        $body = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $params);

        try {
            $this->client->sendGetRequest('sendMessage', $body);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendPhoto($chatId, $photo = null, $caption = '') 
    {
        $body = array_merge([
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
        ]);

        try {
            $this->client->sendGetRequest('sendPhoto', $body);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendVideo($chatId, $video = null, $params = []) 
    {
        $body = array_merge([
            'chat_id' => $chatId,
            'video' => $video,
        ], $params);

        try {
            $this->client->sendGetRequest('sendVideo', $body);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendMediaGroup($chatId, $media = []) 
    {
        $body = array_merge([
            'chat_id' => $chatId,
            'media' => $media,
        ]);

        try {
            $this->client->sendGetRequest('sendMediaGroup', $body);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendPoll($chatId, $question = "", $options = []) 
    {
        $body = array_merge([
            'chat_id' => $chatId,
            'question' => $question,
            'options' => $options,
            'is_anonymous' => false
        ]);

        try {
            $this->client->sendGetRequest('sendPoll', $body);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function setWebhook($url) 
    {
        $body = array_merge([
            'url' => $url,
        ]);

        try {
            $this->client->sendGetRequest('setWebhook', $body);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function sendDeleteMessage($chatId, int $messageId) 
    {
        $body = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];

        try {
            $this->client->sendGetRequest('deleteMessage', $body);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function logError(mixed $error, $chatId = null) 
    {
        if (!$chatId) {
            $chatId = $this->chatIdForLogByDefault;
        }

        try {
            $this->sendMessage($chatId, $error->getMessage() . PHP_EOL . $error->getTraceAsString());
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getChat($chatId) 
    {
        try {
            return json_decode($this->client->sendGetRequest('getChat', ['chat_id' => $chatId]), true);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
