<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Services\DbQueries;
use App\Services\SubscriptionService;
use App\HttpApiAdapters\GuzzleHttpAdapter;
use App\Api\VkApi\VkApi;

#[AsCommand(
    name: 'app:get-girls',
    description: '',
    hidden: false,
    aliases: ['app:get-girls']
)]
class GetGirlsCommand extends Command
{
    public function __construct(
        private SubscriptionService $subService,
        private DbQueries $db,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $vkApi = new VkApi((new GuzzleHttpAdapter('https://api.vk.com/')));
        $resp = $vkApi->get(['code' => 'var responses = [], i = 0; while (i < 1) {  responses = responses + API.wall.get({     "owner_id": "beautifulgirltop", "offset": 1900 + i * 100, "count": 100}).items;   i = i + 1; } return responses;']);

        foreach($resp['response'] ?? [] as $girl) {
            $this->db->createGirl($girl);
        }

        return Command::SUCCESS;
    }
}