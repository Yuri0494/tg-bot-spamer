<?php

namespace App\Services;

use App\TelegramBot\TelegramBot;
use App\Entity\Subscription;
use App\Buttons\ButtonService;
use Exception;

class GirlContentService implements ContentServiceInterface {
    public Subscription $subscription;
    public int $count;
    public int $sleepTime;
    public bool $buttons;

    private function __construct(
        private TelegramBot $bot,
        private GirlService $girlService
    ) {}

    public static function create(TelegramBot $bot, GirlService $girlService): self
    {
        $service = new self($bot, $girlService);

        return $service;
    }
    // Не нравится! :(
    public function setParameters(Subscription $subscription, int $count = 1, $sleepTime = 2, $buttons = true): self
    {
        $this->subscription = $subscription;
        $this->count = $count;
        $this->sleepTime = $sleepTime;

        return $this;
    }
    
    public function send($chatId, $number): bool
    {
        try {
            $girls = $this->girlService->getGirlss($number, $this->count);

            if (empty($girls)) {
                $this->bot->api->sendMessage($chatId, "К сожалению данных не найдено");
                return false;
            }

            foreach($girls as $girl) {
                $mediaGroup = [];
                foreach($girl['img_links'] as $link) {
                    $mediaGroup[] = [
                        'type' => 'photo',
                        'media' => $link,
                    ];
                }
    
                if ($girl['personal_info']) {
                    $this->bot->api->sendMessage($chatId, $girl['personal_info']);
                }
    
                $this->bot->api->sendMediaGroup($chatId, json_encode($mediaGroup));
                $this->bot->api->sendPoll($chatId, 'Как вам девочка?', $this->getStandartPoll());

                if ($this->count > 1) {
                } else {
                    // Если отправляется 1 опрос, то после него требуется выдержать паузу
                    sleep(1);
                    $this->bot->api->sendMessage($chatId, 'Смотрим следующую? ;)', ['reply_markup' => ButtonService::getInlineKeyboardForNextGirls()]);
                }

                sleep($this->sleepTime);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCount(): int
    {
        return $this->girlService->getCountOfGirls();
    }

    private function getStandartPoll()
    {
        return json_encode([                
            [
                'text' => 1,
            ],
            [
                'text' => 2,
            ],
            [
                'text' => 3,
            ],
            [
                'text' => 4,
            ],
            [
                'text' => 5,
            ],
            [
                'text' => 6,
            ],
            [
                'text' => 7,
            ],
            [
                'text' => 8,
            ],
            [
                'text' => 9,
            ],
            [
                'text' => 10,
            ],
        ]);
    }
}