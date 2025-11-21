<?php

use App\Application;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new Application()->http)();
