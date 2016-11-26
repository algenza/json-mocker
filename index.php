<?php
require __DIR__.'/vendor/autoload.php';

$config = include __DIR__.'/src/Config.php';

use Klein\Klein;
use Algenza\Json\Mocker\Resolver;

$klein = new Klein();
Resolver::config($config);

$klein->respond(function ($request, $response, $service) {
    return Resolver::run($request, $response, $service);
    // return $response->dump($request->server()->getHeaders()['HOST']);
    return $response->dump(json_decode(file_get_contents(__DIR__.'/api/db.json')));
});

$klein->dispatch();
