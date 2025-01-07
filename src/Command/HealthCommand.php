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
    name: 'app:health',
    description: 'send message to tg chat',
    hidden: false,
    aliases: ['app:health']
)]
class HealthCommand extends Command
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
        $bot = new TelegramBot(
            new TelegramApi((new GuzzleHttpAdapter('https://api.telegram.org/bot6768896921:AAHSiWv6mmLSdd6b7kLVOIy9XXKltN8KIlg/')))
        );
        // $process = new Process(['ls', '-lsa']);
        // try {
        //     $process->mustRun();

        //     echo $process->getOutput();
        //     $reuslt = $bot->api->setWebhook('https://user353218932-tfcgvelw.tunnel.vk-apps.com/');
        // } catch (Exception $e) {
        //     return Command::SUCCESS;
        // }

        try {
            $bot->api->sendMessage(-1002337503652, 'health check');
        } catch (Exception $e) {
        } finally {
            return Command::SUCCESS;
        }        
    }
}