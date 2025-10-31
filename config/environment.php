<?php

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env');

if ($_ENV["ERROR_DETAILS"]) {
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    error_reporting(0);
}

date_default_timezone_set($_ENV['TIMEZONE']);
