<?php
require __DIR__.'/vendor/autoload.php';

$config = include __DIR__.'/src/Config.php';

use Klein\Klein;
use Algenza\Json\Mocker\Resolver;

$klein = new Klein();
Resolver::config($config);

$klein->respond(function ($request, $response, $service) {
	$response->header('Access-Control-Allow-Origin','*');
	if($request->pathname() !== '/' && $request->headers()->get('Content-Type')!=='application/json' && $request->method()!='GET'){

		$response->code(415);
		$obj = new \stdClass();
		$obj->code = $response->status()->getCode();
		$obj->message = $response->status()->getMessage();
		return $response->json($obj);
	}
    return Resolver::run($request, $response, $service);
});

$klein->dispatch();
