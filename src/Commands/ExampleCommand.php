<?php

declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use tthe\Bagatelle\Misc\Greeter;

/**
 * Example on how to define a command.
 * To enable, it must also be added to "app.console.commands" in config/container.php
 */
#[AsCommand('greet', 'A sample command that prints a friendly greeting.')]
class ExampleCommand extends Command
{
    public function __construct(private readonly Greeter $greeter) {
        parent::__construct();
    }

    public function __invoke(SymfonyStyle $io): int {
        $io->text($this->greeter->greet());
        return Command::SUCCESS;
    }
}