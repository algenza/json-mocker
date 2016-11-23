<?php
require __DIR__.'/vendor/autoload.php';

$config = include __DIR__.'/src/Config.php';

use Klein\Klein;
use Algenza\Json\Mocker\Resolver;

$klein = new Klein();
Resolver::config($config);

$klein->respond(function ($request) {
    echo Resolver::run($request);
    // return Resolver::run($request);
});

$klein->dispatch();
