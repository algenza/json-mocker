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
		if($request->method()=='GET'){
			return self::handleGet($request, $response, $service);
		}else if($request->method()=='POST'){
			return self::handlePost($request, $response, $service);
		}else if($request->method()=='PUT'){
			return self::handlePut($request, $response, $service);
		}else if($request->method()=='DELETE'){
			return self::handleDelete($request, $response, $service);
		}else if($request->method()=='PATCH'){
			return self::handlePatch($request, $response, $service);
		}
		return self::notFound($response);		
	}
	private static function handleGet(Request $request, Response $response, ServiceProvider $service){
		if($request->pathname() == '/'){
			$service->data = self::processJson();
			return self::render($service,'home');
		}

		$uriPart = self::extracUri($request->pathname());

		if($uriPart[0]=='db'){
			$output = self::processJson();
			return $response->json($output);
		}

		if(self::isValidUri($uriPart)){
			return self::takeData($response, $uriPart, $request->paramsGet());
		}

		return self::notFound($response);		
	}
	private static function handlePost(Request $request, Response $response, ServiceProvider $service){
		if($request->pathname() == '/'){
			return self::notFound($response);
		}
		$uriPart = self::extracUri($request->pathname());
		if(self::isValidUri($uriPart, true)){
			return self::saveData($response, $uriPart, $request->body());
		}

		return self::notFound($response);		
	}
	private static function saveData($response, $uriPart, $params){
		$fullFile = json_decode(file_get_contents(self::$targetJson));
		$maxid = 1;
		if(isset($fullFile->{$uriPart[0]})){
			foreach ($fullFile->{$uriPart[0]} as $item) {
				if($item->id > $maxid){
					$maxid = $item->id;
				}
			}			
		}
		$newdata = new \stdClass();;
		$newdata->id = (int)$maxid+1;
		$data = json_decode($params);
		if($data->id){
			unset($data->id);
		}
		foreach ($data as $key => $value) {
			$newdata->{$key} = $value; 
		}
		$fullFile->{$uriPart[0]}[] = $newdata;
		file_put_contents(self::$targetJson,json_encode($fullFile));

		return $response->json($newdata);		
	}
	private static function handlePut(Request $request, Response $response, ServiceProvider $service){
		$obj = new \stdClass();
		$obj->message = "You hit put method";
		return $response->json($obj);		
	}
	private static function handleDelete(Request $request, Response $response, ServiceProvider $service){
		$obj = new \stdClass();
		$obj->message = "You hit delete method";
		return $response->json($obj);		
	}
	private static function handlePatch(Request $request, Response $response, ServiceProvider $service){
		$obj = new \stdClass();
		$obj->message = "You hit patch method";
		return $response->json($obj);		
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

	private static function isValidUri($uriPart, $forPost = false)
	{
		$fullFile = json_decode(file_get_contents(self::$targetJson));
		if(!isset($fullFile->{$uriPart[0]})){
			return false;
		}
		if (!$forPost) {
			if(isset($uriPart[1])){
				foreach ($fullFile->{$uriPart[0]} as $item) {
					if($item->id == $uriPart[1]){
						return true;
					}
				}
				return false;
			}
		} else {
			if(isset($uriPart[1])){
				return false;
			}
		}

		return true;
	}

	private static function takeData($response, $uriPart, $params = null)
	{
		$fullFile = json_decode(file_get_contents(self::$targetJson));
		if(!isset($uriPart[1])){
			if(!$params){
				return $response->json($fullFile->{$uriPart[0]});
			}

			$filteredData = array_filter($fullFile->{$uriPart[0]}, function ($obj) use ($params){
				foreach ($params as $key => $value) {
					if(isset($obj->{$key})){
						if(strpos(strtolower($obj->{$key}),strtolower($value))===false){
							return false;
						}
					}else{
						return false;
					}
				}
				return true;
			});

			return $response->json($filteredData);
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