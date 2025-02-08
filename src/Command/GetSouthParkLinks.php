<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Api\TelegramApi\TelegramApi;
use App\TelegramBot\TelegramBot;
use App\HttpApiAdapters\GuzzleHttpAdapter;
use App\Repository\GirlRepository;
use App\Repository\SketchesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;
use Exception;

#[AsCommand(
    name: 'app:links',
    description: 'send message to tg chat',
    hidden: false,
    aliases: ['app:health']
)]
class GetSouthParkLinks extends Command
{

    public function __construct(
        private GirlRepository $gr,
        private SketchesRepository $sr,
        private EntityManagerInterface $em,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {   
        $soundParams = [
            '_par.mp4',
            '_goblin.mp4',
            '_mtv.mp4',
        ];
        $result = [

        ];
        $queryService = new GuzzleHttpAdapter('https://free.freehat.cc/sp/free/');
        $template = 'https://free.freehat.cc/sp/free/';
        $season = 1; 
        for($i = 100; $i <= 300; $i+=100) {
            for($g = 1; $g <= 19; $g++) {
                $number = $i + $g;
                $link = $template . (string) $number . $soundParams[1];
                try {
                    $queryService->sendQueryAndGetStatusCode((string) $number . $soundParams[1]);
                    $result[$season][] = $link;
                } catch (Exception $e) {
                    continue;
                }
            }
            $season++;
        }

        return Command::SUCCESS;   
    }
}