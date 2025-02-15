<?php

namespace App\Api\VkApi;

use App\HttpApiAdapters\HttpAdapterInterface;

class VkApi {
    private HttpAdapterInterface $client;
    private array $commonParams;

    public function __construct(HttpAdapterInterface $client)
    {
        $this->client = $client;
        $this->commonParams = [
            'access_token' => '5881f71e5881f71e5881f71e835ba51488558815881f71e3fc124432ed376a09b09c338',
            'v' => '5.199'
        ];
    }

    public function wallGet(array $params = [])
    {
        return json_decode($this->client->sendGetRequest('method/wall.get', array_merge($this->commonParams, $params)), true);
    }
    public function get(array $params = [])
    {
        return json_decode($this->client->sendGetRequest('method/execute', array_merge($this->commonParams, $params)), true);
    }
}
