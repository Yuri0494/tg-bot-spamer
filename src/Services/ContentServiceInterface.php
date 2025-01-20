<?php

namespace App\Services;

use App\Entity\Subscription;

interface ContentServiceInterface {
    public function setParameters(Subscription $subscription, int $count = 1, $sleepTime = 2, $buttons = true): self;
    public function send($chatId, $number): bool;
    public function getCount(): int;
}