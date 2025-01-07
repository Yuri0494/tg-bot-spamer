<?php

namespace App\WheatherApiAdapters;

interface WheatherApiInterface {
   public function getWheatherInfo(string $area): array;
}