<?php

use Symfony\Component\Dotenv\Dotenv;

$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    die('Error! No .env exists. Create a copy from .env.example.');
}

$dotenv = new Dotenv();
$dotenv->load($envFile);

if ($_ENV["BAGATELLE_DETAILED_ERRORS"]) {
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    error_reporting(0);
}
