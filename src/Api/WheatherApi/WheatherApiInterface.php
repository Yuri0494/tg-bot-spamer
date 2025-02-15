<?php

namespace App\Api\WheatherApi;

interface WheatherApiInterface {
   public function getWheatherInfo(string $area): array;
}