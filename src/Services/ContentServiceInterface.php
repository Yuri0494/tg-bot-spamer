<?php

namespace App\Services;

use App\Entity\Subscription;

interface ContentServiceInterface {
    public function setParameters(Subscription $subscription, int $count = 1, $sleepTime = 2): self;
    public function send($chatId, $number, $buttonsAfterSend = true): bool;
    public function getCount(): int;
}