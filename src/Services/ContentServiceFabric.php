<?php

namespace App\Services;

use App\TelegramBot\TelegramBot;

class ContentServiceFabric {
    public function __construct(        
        private TelegramBot $bot,
        private SketchService $sketchService,
        private GirlService $girlService
    ) {}

    public function getContentService($category): ContentServiceInterface
    {
        switch($category) {
            case 'video':
                return VideoContentService::create($this->bot, $this->sketchService);
            case 'girl':
                return GirlContentService::create($this->bot, $this->girlService);
        }
    }
}