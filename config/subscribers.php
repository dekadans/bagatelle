<?php

/*
 * Add all your HTTP request event subscribers here.
 * They should all implement Symfony\Component\EventDispatcher\EventSubscriberInterface
 */
return [
    \App\Services\Auth\AuthenticationSubscriber::class
];