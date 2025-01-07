<?php

namespace App\Api\WheatherApiAdapters;

use Exception;
use App\HttpApiAdapters\HttpAdapterInterface;
use App\HttpApiAdapters\GuzzleHttpAdapter;

final class WheatherApiFree implements WheatherApiInterface {
    private string $token = '9PXX6XAW4DVP5ZW4KDKA4LZ6R'; // Токены и другие приватные данные должны добавляться через переменные окружения или конфигурации к приложению
    private const URL = 'https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/';
    private HttpAdapterInterface $httpClient;

    public function __construct()
    {
        $this->httpClient = new GuzzleHttpAdapter(WheatherApiFree::URL);
    }

    public function getWheatherInfo(string $area): array
    {   
        try {
            return json_decode($this->httpClient->sendGetRequest($area, ['key' => $this->token]), true);
        } catch (Exception $e) {
            return [];
        }
    }
}