<?php
namespace Algenza\Json\Mocker;

use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

use Algenza\Json\Mocker\Repository;

class Resolver
{
	private static $initialized = false;
	private static $schemaPath;
	private static $targetJson;
	private static $viewPath;
	private static $limit;
	private static $repository;

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
    	self::$repository = new Repository(self::$targetJson);
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
		return self::error($response);		
	}

	private static function handleGet(Request $request, Response $response, ServiceProvider $service){
		if($request->pathname() == '/'){
			$service->data =  self::$repository->getAll();
			return self::render($service,'home');
		}

		$uriPart = self::extracUri($request->pathname());

		if($uriPart[0]=='db'){
			$output =  self::$repository->getAll();
			return $response->json($output);
		}

		if(self::isValidUri($uriPart)){
			return self::takeData($response, $uriPart, $request->paramsGet());
		}

		return self::error($response);		
	}

	private static function handlePost(Request $request, Response $response, ServiceProvider $service){
		if($request->pathname() == '/'){
			return self::error($response);
		}

		$uriPart = self::extracUri($request->pathname());
		if(self::isValidUri($uriPart, true)){
			try {
				$result =  self::$repository->addData($uriPart[0], $request->body());				
				$response->code(201);
				return $response->json($result);				
			} catch (\Exception $e) {
				return self::error($response, 500);
			}
		}

		return self::error($response);		
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
	private static function error($response, $code = 404){
		$response->code($code);
		$obj = new \stdClass();
		$obj->code = $response->status()->getCode();
		$obj->message = $response->status()->getMessage();
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
		$fullFile = self::$repository->getAll();
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
		if(!isset($uriPart[1])){
			$data = self::$repository->getDataList($uriPart[0], $params);
			return $response->json($data);
		}

		$data = self::$repository->getData($uriPart[0], $uriPart[1]);
		if(!$data){
			return self::error($response);
		}
		return $response->json($data);
	}

	private static function render($service, $view = 'home', array $data = []){
		$viewFile = self::$viewPath.$view.'.php';
		foreach ($data as $key => $value) {
			$service->{$key} = $value;
		}
		return $service->render($viewFile);
	}
}