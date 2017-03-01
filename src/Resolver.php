<?php
namespace Algenza\Json\Mocker;

use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

use Algenza\Json\Mocker\Repository;
use Algenza\Json\Mocker\Validator;

class Resolver
{
	private static $initialized = false;
	private static $schemaPath;
	private static $targetJson;
	private static $viewPath;
	private static $limit;
	private static $repository;
	private static $validator;
	private static $methodRequireContent = ['POST','PUT','PATCH'];

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
    	self::$validator = new Validator;
    }

	public static function run(Request $request, Response $response, ServiceProvider $service)
	{
		self::initialize();
		if(!self::$validator->isValidJson($request->body()) && in_array($request->method(), self::$methodRequireContent)){
			return self::httpResponse($response,400);	
		}

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
		}else if($request->method()=='OPTIONS'){
			return self::httpResponse($response,200);		
		}
		return self::httpResponse($response);		
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

		if(self::$validator->isValidUri(self::$repository, $uriPart)){
			return self::takeData($response, $uriPart, $request->paramsGet());
		}

		return self::httpResponse($response);		
	}

	private static function handlePost(Request $request, Response $response, ServiceProvider $service){
		if($request->pathname() == '/'){
			return self::httpResponse($response);
		}

		$uriPart = self::extracUri($request->pathname());
		if(self::$validator->isValidUri(self::$repository, $uriPart, true)){
			try {
				$result =  self::$repository->addData($uriPart[0], $request->body());				
				$response->code(201);
				return $response->json($result);				
			} catch (\Exception $e) {
				return self::httpResponse($response, 500);
			}
		}

		return self::httpResponse($response);		
	}

	private static function handlePut(Request $request, Response $response, ServiceProvider $service){

		if($request->pathname() == '/'){
			return self::httpResponse($response);
		}

		$uriPart = self::extracUri($request->pathname());
		if(self::$validator->isValidUri(self::$repository, $uriPart)){
			if($request->headers()->get('Content-Length')==0){
				return self::httpResponse($response, 400);
			}

			try {
				$result =  self::$repository->fullUpdate($uriPart[0], $uriPart[1],$request->body());				
				return $response->json($result);				
			} catch (\Exception $e) {
				return self::httpResponse($response, 500);
			}
		}

		return self::httpResponse($response);			
	}

	private static function handleDelete(Request $request, Response $response, ServiceProvider $service){
		if($request->pathname() == '/'){
			return self::httpResponse($response);
		}

		$uriPart = self::extracUri($request->pathname());
		if(self::$validator->isValidUri(self::$repository, $uriPart)){
			try {
				$result =  self::$repository->delete($uriPart[0], $uriPart[1]);				
				return self::httpResponse($response, 204);
			} catch (\Exception $e) {
				return self::httpResponse($response, 500);
			}
		}

		return self::httpResponse($response);		
	}

	private static function handlePatch(Request $request, Response $response, ServiceProvider $service){

		if($request->pathname() == '/'){
			return self::httpResponse($response);
		}

		$uriPart = self::extracUri($request->pathname());
		if(self::$validator->isValidUri(self::$repository, $uriPart)){
			if($request->headers()->get('Content-Length')==0){
				return self::httpResponse($response, 400);
			}

			try {
				$result =  self::$repository->partialUpdate($uriPart[0], $uriPart[1],$request->body());				
				return $response->json($result);				
			} catch (\Exception $e) {
				return self::httpResponse($response, 500);
			}
		}

		return self::httpResponse($response);		
	}	

	private static function httpResponse($response, $code = 404, $data = null){
		$response->code($code);
		$obj = new \stdClass();
		$obj->code = $response->status()->getCode();
		$obj->message = $response->status()->getMessage();

		if(!is_null($data)){
			$obj = $data;
		}

		return $response->json($obj);
	}

	private static function extracUri($uri)
	{
		$uriPart = substr($uri,1);
		$uriPart = explode('/',$uriPart);
		return $uriPart;
	}

	private static function takeData($response, $uriPart, $params = null)
	{
		if(!isset($uriPart[1])){
			$data = self::$repository->getDataList($uriPart[0], $params);
			return $response->json($data);
		}

		$data = self::$repository->getData($uriPart[0], $uriPart[1]);
		if(!$data){
			return self::httpResponse($response);
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
