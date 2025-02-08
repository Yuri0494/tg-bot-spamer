<?php

namespace App\HttpApiAdapters;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

final class GuzzleHttpAdapter implements HttpAdapterInterface {

    private ClientInterface $client;

    public function __construct($base_url)
    {
        $this->client = new Client(['base_uri' => $base_url]);
    }

    public function sendGetRequest(string $uri, $params = [], $headers = [])
    {
        return $this->client->request('GET', $uri, 
        [
            'query' => $params,
            'headers' => $headers
        ],
        )->getBody();
    }

    public function sendPostRequest(string $uri, $params = [], $headers = [])
    {
        return $this->client->request('POST', $uri, 
        [
            'body' => $params,
            'headers' => $headers
        ]
        )->getBody();
    }

    public function sendQueryAndGetStatusCode(string $uri, $params = [], $headers = [])
    {
        return $this->client->request('HEAD', $uri, 
        [
            'query' => $params,
            'headers' => $headers
        ],
        )->getStatusCode();
    }
}