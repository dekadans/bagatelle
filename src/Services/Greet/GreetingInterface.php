<?php

namespace App\Services\Greet;

/**
 * A simple service interface that's used in the default Bagatelle welcome page and example console command.
 */
interface GreetingInterface
{
    /**
     * Generate a friendly greeting phrase :)
     * @return string
     */
    public function greet(): string;
}