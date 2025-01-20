<?php

namespace App\Controller;

use App\Server\Server;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    public function index(Server $server): Response
    {
        $server->handleRequest();

        return new Response();
    }
}