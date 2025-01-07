<?php

namespace App\Buttons;
use App\Entity\Subscription;

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

        $buttons[][] = Button::create('Смотреть', '/see')->toArray();

        return json_encode([
                'inline_keyboard' => $buttons
            ]);
    }

    public static function getInlineKeyboardForSee(array $subscriptions): string
    {
        $buttons = [];
        foreach($subscriptions as $subscription) {
            $code = str_replace('/', '/see-', $subscription->getCode());
            $buttons[][] = Button::create($subscription->getName(), $code)->toArray();
        }

        $buttons[][] = Button::create('В начало', '/start')->toArray();

        return json_encode([
                'inline_keyboard' => $buttons
            ]);
    }

    public static function getInlineKeyboardAfterSubscribe(): string
    {
        return json_encode([
                'inline_keyboard' => [
                    [
                        Button::create('В начало', '/start')->toArray(),
                    ],
                    [
                        Button::create('Смотреть', '/see')->toArray(),
                    ],
                ]
            ]);
    } 
}