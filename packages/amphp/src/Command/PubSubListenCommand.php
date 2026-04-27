<?php

declare(strict_types=1);

namespace Marko\Amphp\Command;

use Marko\Core\Attributes\Command;
use Marko\Core\Command\CommandInterface;
use Marko\Core\Command\Input;
use Marko\Core\Command\Output;

#[Command(name: 'pubsub:listen', description: 'Listen to PubSub events using AmpHP')]
class PubSubListenCommand implements CommandInterface
{
    public function execute(Input $input, Output $output): int
    {
        $output->writeLine('Starting PubSub listener...');
        
        // Skeleton for AmpHP PubSub listener
        
        return 0;
    }
}
