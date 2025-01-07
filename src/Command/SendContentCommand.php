<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Services\SubscriptionService;
use Exception;

#[AsCommand(
    name: 'app:send-content',
    description: 'send content to tg chats',
    hidden: false,
    aliases: ['app:send-content']
)]
class SendContentCommand extends Command
{
    public function __construct(
        private SubscriptionService $subService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->subService->sendHello();
        $this->subService->sendContentToSubscribers();
        return Command::SUCCESS;
    }
}