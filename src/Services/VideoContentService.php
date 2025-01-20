<?php

namespace App\Services;

use App\TelegramBot\TelegramBot;
use App\Entity\Subscription;
use App\Buttons\ButtonService;
use Exception;

class VideoContentService implements ContentServiceInterface {
    private Subscription $subscription;
    private bool $buttons;

    private function __construct(
        private TelegramBot $bot,
        private SketchService $sketchService
    ) {}

    public static function create(TelegramBot $bot, SketchService $sketchService): self
    {
        $service = new self($bot, $sketchService);

        return $service;
    }

    public function setParameters(Subscription $subscription, int $count = 1, $sleepTime = 2, $buttons = true): self
    {
        $this->subscription = $subscription;
        $this->buttons = $buttons;

        return $this;
    }

    public function getCount(): int
    {
        return $this->sketchService->getSeriesCountOfSketch($this->subscription->getCodeWithoutSlash());
    }
    
    public function send($chatId, $number): bool
    {
        try {
            $link = $this->sketchService->getSketchLink($this->subscription->getCodeWithoutSlash(), $number);

            if (!$link) {
                $this->bot->api->sendMessage(
                    $chatId, 
                    "Я прислал вам все серии " . $this->subscription->getName() . "." . PHP_EOL . 'Вы можете попробовать подписаться на что-то еще. Для этого отправьте /start'
                );

                return false;
            }

            $this->bot->api->sendMessage($chatId, $link, $this->buttons ? ['reply_markup' => ButtonService::getInlineKeyboardForNextSeries($number)] : []);

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

}