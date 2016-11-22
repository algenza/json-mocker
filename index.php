<?php

require __DIR__.'/vendor/autoload.php';

use Klein\Klein;

$klein = new Klein();

$klein->respond(function ($request) {
    return $request->uri();
});

$klein->dispatch();
