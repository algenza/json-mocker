<?php
require __DIR__.'/vendor/autoload.php';

$config = include __DIR__.'/src/Config.php';

use Klein\Klein;
use Algenza\Json\Mocker\Resolver;

$klein = new Klein();
Resolver::config($config);

$klein->respond(function ($request, $response, $service) {
	$filteredMethod = ['PUT','PATCH','POST'];
	$response->header('Access-Control-Allow-Origin','*');
	$response->header('Access-Control-Allow-Headers','Content-Type');
	$response->header('Access-Control-Allow-Methods','GET, POST, PUT');
	file_put_contents('request.json', $request->headers()->get('Content-Type'));
	if($request->pathname() !== '/' 
		&& $request->headers()->get('Content-Type')!=='application/json'
		&& in_array($request->method(),$filteredMethod)
		){

		$response->code(415);
		$obj = new \stdClass();
		$obj->code = $response->status()->getCode();
		$obj->message = $response->status()->getMessage();
		$obj->additional = $request->headers()->get('Content-Type');
		return $response->json($obj);
	}
    return Resolver::run($request, $response, $service);
});

$klein->dispatch();
