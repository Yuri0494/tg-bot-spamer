<?php

namespace App\WheatherApiAdapters;

use Exception;

final class Wheather {
    private string $datetime;
    private int $temp;
    private int $feelslike;
    // Влажность
    private int $humidity;
    // Давление
    private int $pressure;
    private string | int $sunrise;
    private string | int $sunset;


    private function __construct()
    {
    }

    public static function createByWAFData(array $data): Wheather
    {
        $wheaterResponse = new Wheather();

        if(!isset($data['currentConditions'])) {
            throw new Exception('Данные о погоде не найдены');
        }

        $WAFInfo = $data['currentConditions'];

        $wheaterResponse->datetime = $WAFInfo['datetime'];
        $wheaterResponse->temp = round(0.6 * ($WAFInfo['temp'] - 32));
        $wheaterResponse->feelslike = round(0.6 * ($WAFInfo['feelslike'] - 32));
        $wheaterResponse->humidity = $WAFInfo['humidity'];
        $wheaterResponse->pressure = $WAFInfo['pressure'];
        $wheaterResponse->sunrise = $WAFInfo['sunrise'];
        $wheaterResponse->sunset = $WAFInfo['sunset'];

        return $wheaterResponse;
    }

    public function toArray()
    {
        return [
            'datetime' => $this->datetime,
            'temp' => $this->temp,
            'feelslike' => $this->feelslike,
            'humidity' => $this->humidity,
            'pressure' => $this->pressure,
            'sunrise' => $this->sunrise,
            'sunset' => $this->sunset,
        ];
    }

    public function toMessage($city)
    {

        $r1 = "Темература в данном городе " . "(" . $city . ")" . " составляет " . $this->temp . " градусов" . "\n";
        $r2 = "Ощущается как " . $this->feelslike . " градусов" . "\n";
        $r3 = "Рассвет: " . $this->sunrise . "\n";
        $r4 = "Закат: " . $this->sunset . "\n";
        $r5 = "Влажность: " . $this->humidity . "\n";
        $r6 = "Давление: " . $this->pressure . " мм р.с" . "\n";
        return $r1 . $r2 . $r3 . $r4 . $r5 . $r6;
    }
}