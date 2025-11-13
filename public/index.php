<?php

use App\Services\Application;

require_once __DIR__ . '/../vendor/autoload.php';

(new Application()->http)();