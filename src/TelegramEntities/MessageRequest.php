<?php

namespace App\TelegramEntities;

use App\Entity\User;
use App\Entity\Chat;

class MessageRequest {
        public int $message_id;
        public User $from;
        public ?Chat $chat;
        public int $date;
        public ?string $text;
        public ?array $entities;
        public ?array $caption_entitie;
        public ?array $audio;
        public ?array $poll;
        public ?array $dice;
        public ?array $sticker;
        public ?array $document;
}