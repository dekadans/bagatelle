<?php

namespace App\Commands;

use App\Services\Greet\GreetingInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GreetingCommand extends Command
{
    public const NAME = 'greet';

    public function __construct(
        readonly private GreetingInterface $greeting
    ) {
        parent::__construct(self::NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->greeting->greet());
        return Command::SUCCESS;
    }
}