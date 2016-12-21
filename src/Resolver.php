<?php
namespace Algenza\Json\Mocker;

use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

class Resolver
{
	private static $initialized = false;
	private static $schemaPath;
	private static $targetJson;
	private static $viewPath;
	private static $limit;

    private static function initialize()
    {
        if (self::$initialized)
            return;
        self::$initialized = true;
    }

    public static function config(array $config)
    {
    	self::initialize();
    	foreach ($config as $key => $value) {
    		self::${$key} = $value;
    	}
    }

	public static function run(Request $request, Response $response, ServiceProvider $service)
	{
		self::initialize();

		if($request->uri() == '/'){
			$service->data = self::processJson();
			return self::render($service,'home');
		}

		$uriPart = self::extracUri($request->uri());

		if($uriPart[0]=='db'){
			$output = self::processJson();
			return $response->json($output);
		}

		if(self::isValidUri($uriPart)){
			return self::takeData($response, $uriPart);
		}

		return self::notFound($response);
	}

	private static function notFound($response){
		$response->code(404);
		$obj = new \stdClass();
		$obj->error = 404;
		$obj->message = 'Page Not Found';
		return $response->json($obj);
	}

	private static function extracUri($uri)
	{
		$uriPart = substr($uri,1);
		$uriPart = explode('/',$uriPart);
		return $uriPart;
	}

	private static function isValidUri($uriPart)
	{
		$fullFile = json_decode(file_get_contents(self::$targetJson));
		if(!isset($fullFile->{$uriPart[0]})){
			return false;
		}

		if(isset($uriPart[1])){
			foreach ($fullFile->{$uriPart[0]} as $item) {
				if($item->id == $uriPart[1]){
					return true;
				}
			}
			return false;
		}

		return true;
	}

	private static function takeData($response, $uriPart)
	{
		$fullFile = json_decode(file_get_contents(self::$targetJson));
		if(!isset($uriPart[1])){
			return $response->json($fullFile->{$uriPart[0]});
		}

		foreach ($fullFile->{$uriPart[0]} as $item) {
			if($item->id == $uriPart[1]){
				return $response->json($item);
			}
		}
	}

	private static function processJson($scope = 'db', $id = null)
	{
		$fullFile = json_decode(file_get_contents(self::$targetJson));
		if($scope == 'db'){
			return $fullFile;
		}
	}

	private static function render($service, $view = 'home', array $data = []){
		$viewFile = self::$viewPath.$view.'.php';
		foreach ($data as $key => $value) {
			$service->{$key} = $value;
		}
		return $service->render($viewFile);
	}
}