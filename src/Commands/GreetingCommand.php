<?php

namespace App\Commands;

use App\Services\GreetingInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Example on how to define a command using the attribute-based syntax.
 * All constructor parameters are resolved through the dependency injection container.
 */
#[AsCommand('greet', 'A sample command that prints a friendly greeting.')]
class GreetingCommand extends Command
{
    public function __construct(
        readonly private GreetingInterface $greeting
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(description: 'The number of greetings to print.')] int $number = 1
    ): int {
        for ($i = 0; $i < $number; $i++) {
            $io->text($this->greeting->greet());
        }
        return Command::SUCCESS;
    }
}