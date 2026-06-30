<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use tthe\Bagatelle\Misc\Greeter;

/**
 * Example on how to define a command.
 */
#[AsCommand('greet', 'A sample command that prints a friendly greeting.')]
class ExampleCommand extends Command
{
    public function __invoke(Greeter $greeter, SymfonyStyle $io): int {
        $io->text($greeter->greet());
        return Command::SUCCESS;
    }
}