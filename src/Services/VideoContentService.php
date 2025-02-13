<?php

namespace App\Services;

use App\TelegramBot\TelegramBot;
use App\Entity\Subscription;
use App\Buttons\ButtonService;
use Exception;

class VideoContentService implements ContentServiceInterface {
    private Subscription $subscription;

    private function __construct(
        private TelegramBot $bot,
        private SketchService $sketchService
    ) {}

    public static function create(TelegramBot $bot, SketchService $sketchService): self
    {
        $service = new self($bot, $sketchService);

        return $service;
    }

    public function setParameters(Subscription $subscription, int $count = 1, $sleepTime = 2): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getCount(): int
    {
        return $this->sketchService->getSeriesCountOfSketch($this->subscription->getCodeWithoutSlash());
    }
    
    public function send($chatId, $number, $buttonsAfterSend = true): bool
    {
        try {
            $link = $this->sketchService->getSketchLink($this->subscription->getCodeWithoutSlash(), $number);
            $name = $this->subscription->getName();
            if (!$link) {
                $this->bot->api->sendMessage(
                    $chatId, 
                    "Я прислал вам все серии " . $name . "." . PHP_EOL . 'Вы можете попробовать подписаться на что-то еще. Для этого отправьте /start'
                );

                return false;
            }

            $this->bot->api->sendMessage(
                $chatId, 
                "$name. Серия: $number" . PHP_EOL . $link, 
                $buttonsAfterSend 
                    ? ['reply_markup' => ButtonService::getInlineKeyboardForNextSeries($number),] 
                    : []
                );

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

}