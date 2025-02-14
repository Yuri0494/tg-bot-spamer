<?php

namespace App\TelegramBot;

use App\Api\TelegramApi\TelegramApi;

class TelegramBot {
    public TelegramApi $api;

    public function __construct($clientApi)
    {
        $this->api = $clientApi;
    }
}