<?php

namespace App\Controller;

use App\Server\Server;
use App\TelegramBotRequest\TelegramBotRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class DefaultController extends AbstractController
{
    public function index(
        Server $server,
        TelegramBotRequest $req,
        ): Response
    {
        try {

            if ($req->type === 'not_handled') {
                return new Response();
            }
            
            $server->handleRequest();
        } catch (Exception $e) {
            
        } finally {
            return new Response();
        }
    }
}