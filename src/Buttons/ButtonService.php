<?php

namespace App\Buttons;

use App\Entity\Subscription;
use App\Server\Server;

final class ButtonService {

    private function __construct()
    {

    }

    public static function getInlineKeyboardForStart(array $subscriptions): string
    {
        $buttons = [];
        foreach($subscriptions as $subscription) {
            $buttons[][] = Button::create($subscription->getName(), $subscription->getCode())->toArray();
        }

        $buttons[][] = Button::create('Смотреть', Server::GET_COMMAND)->toArray();

        return json_encode([
                'inline_keyboard' => $buttons
            ]);
    }

    public static function getInlineKeyboardForCurrentChat(array $subscriptions): string
    {
        $buttons = [];
        foreach($subscriptions as $subscription) {
            $code = str_replace('/', Server::GET_THIS_COMMAND, $subscription->getCode());
            $buttons[][] = Button::create($subscription->getName(), $code)->toArray();
        }

        $buttons[][] = Button::create('В начало', Server::START_COMMAND)->toArray();

        return json_encode([
                'inline_keyboard' => $buttons
            ]);
    }

    public static function getInlineKeyboardAfterSubscribe(): string
    {
        return json_encode([
                'inline_keyboard' => [
                    [
                        Button::create('В начало', Server::START_COMMAND)->toArray(),
                    ],
                    [
                        Button::create('Смотреть', Server::GET_COMMAND)->toArray(),
                    ],
                ]
            ]);
    } 
}