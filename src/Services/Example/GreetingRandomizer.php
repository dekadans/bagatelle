<?php

namespace App\Services\Example;

class GreetingRandomizer implements GreetingInterface
{
    public function greet(): string
    {
        return 'Hello';
    }
}