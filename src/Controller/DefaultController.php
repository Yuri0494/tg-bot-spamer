<?php

namespace App\Controller;

use App\Server\Server;
use App\TelegramBot\TelegramBot;
use App\TelegramBotRequest\TelegramBotRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    public function index(
        Server $server,
        TelegramBotRequest $req,
        TelegramBot $tgBot
        ): Response
    {
        try {

            if ($req->type === 'not_handled') {
                // Костыль, который помогает избежать попыток ответить на запрос от телеграма, который приложение не умеет обрабатывать
                return new Response();
            }
            
            $server->handleRequest();
        } catch (\Throwable $e) {
            $tgBot->api->logError($e);
        } finally {
            // В любом случае возвращаем телеграму положительный ответ на его запрос по веб-хуку.
            return new Response();
        }
    }
}