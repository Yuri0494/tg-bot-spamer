<?php

namespace App\Api\WheatherApiAdapters;

interface WheatherApiInterface {
   public function getWheatherInfo(string $area): array;
}