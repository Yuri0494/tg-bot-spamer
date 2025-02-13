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
use App\Services\SubscriptionService;
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
        private SubscriptionService $subscriptionService,
        private EntityManagerInterface $em,
        private TelegramBot $bot,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {   
        // $b = 'https://s1.streamani.top/video1/Jja5enQxh-eNMByqknCnBQ/1739053070/southpark/19/paramount/1910.mp4';
        // $c = 'https://s1.streamani.top/video1/Jja5enQxh-eNMByqknCnBQ/1739053070/southpark/19/paramount/1910.mp4';
        // $m = 'https://s5.streamani.top/video1/54S5Mf1plXJxm50Qam4Jeg/1739052930/southpark/1/goblin/101.mp4';
        // $t = 'https://s6.streamani.top/video1/s3U6agRHirhohWSu_SAjSw/1739057234/southpark/3/mtv/301.mp4';
        // $result = [

        // ];
        // $mainLink = 'https://s1.streamani.top/video1/Jja5enQxh-eNMByqknCnBQ/1739053070/southpark/';
        // $queryService = new GuzzleHttpAdapter($mainLink);
        // $voice = 'paramount';
        // $season = 1; 
        // for($i = 500; $i <= 1900; $i+=100) {
        //     for($g = 1; $g <= 19; $g++) {
        //         $number = $i + $g;
        //         $season = $i / 100;
        //         $uri = "$season/$voice/$number.mp4";
        //         try {
        //             $status = $queryService->sendQueryAndGetStatusCode($uri);
        //             if ($status === 200) {
        //                 $result[$season][] = $mainLink . $uri;
        //                 echo $mainLink . $uri . PHP_EOL;
        //             }
        //         } catch (Exception $e) {
        //             continue;
        //         }
        //     }
        //     $season++;
        // }
        // $this->em->beginTransaction();
        // try {
        //     $this->subscriptionService->publishSketches($result, 'Южный парк', 'south');
        //     $this->em->commit();
        // } catch (Exception $e) {
        //     $this->em->rollback();
        // }
        $this->bot->api->sendMessage(788788415, '[Внешняя ссылка](https://stepik.org/120924)', ['parse_mode' => 'MarkdownV2']);
        // var_dump($result);
        return Command::SUCCESS;   
    }
}