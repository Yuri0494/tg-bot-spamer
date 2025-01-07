<?php

namespace App\Buttons;

class Button {
    private string $text;
    private string $callbackData;

    private function __construct(string $text, string $callbackData)
    {
        $this->text = $text;
        $this->callbackData = $callbackData;
    }

    public static function create(string $text, string $callbackData): Button
    {
        return new Button($text, $callbackData);
    }

    public function toArray()
    {
        return  [
            'text' => $this->text,
            'callback_data' => $this->callbackData
        ];
    }
};