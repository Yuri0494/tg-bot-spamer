<?php

namespace App\HttpApiAdapters;

interface HttpAdapterInterface {
    public function sendGetRequest(string $uri, $params = [], $headers = []);
    public function sendPostRequest(string $uri, $body = [], $headers = []);
}