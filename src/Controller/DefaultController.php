<?php

namespace App\Controller;

use App\Server\Server;
use App\Services\DbQueries;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\HttpApiAdapters\GuzzleHttpAdapter;
use App\TelegramBotRequest\TelegramBotRequest;
use App\Api\VkApi\VkApi;

class DefaultController extends AbstractController
{
    public function index(
        Server $server, 
        DbQueries $db
    ): Response
    {
        // $vkApi = new VkApi((new GuzzleHttpAdapter('https://api.vk.com/')));
        // $resp = $vkApi->get(['code' => 'var responses = [], i = 0; while (i < 10) {  responses = responses + API.wall.get({     "owner_id": "beautifulgirltop", "offset": 2052 + i * 100, "count": 100}).items;   i = i + 1; } return responses;']);

        // foreach($resp['response'] ?? [] as $girl) {
        //     $db->createGirl($girl);
        // }
        $server->handleRequest();

        // $db->deleteGirls();
        return new Response();
    }
}