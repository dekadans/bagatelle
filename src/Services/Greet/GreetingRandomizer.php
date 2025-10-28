<?php

namespace App\Services\Greet;

class GreetingRandomizer implements GreetingInterface
{
    public function greet(): string
    {
        $greetings = [
            'Hello',
            'Hi',
            'Hey',
            'Good morning',
            'Good afternoon',
            'Good evening',
            'How are you?',
            'How\'s it going?',
            'What\'s up?',
            'Howdy',
            'Greetings',
            'Welcome',
            'Nice to see you',
            'Long time no see',
            'How have you been?',
            'Good to see you',
            'Pleased to meet you',
            'How do you do?',
            'Hey there',
            'What\'s new?'
        ];

        $random = array_rand($greetings);
        return $greetings[$random];
    }
}