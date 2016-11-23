<?php
namespace Algenza\Json\Mocker;

use Klein\Request;

class Resolver
{
	private static $initialized = false;
	private static $schemaPath;
	private static $targetJson;

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

	public static function run(Request $request)
	{
		self::initialize();
		$uriPart = self::extracUri($request->uri());

		if($uriPart[0]=='db'){
			$output = self::processJson();
			return self::wrapJsonOutput($output);
		}else{

		}

		return '404';
	}

	private static function extracUri($uri)
	{
		$uriPart = substr($uri,1);
		$uriPart = explode('/',$uriPart);
		return $uriPart;
	}

	private static function processJson($scope = 'db', $id = null)
	{
		$fullFile = json_decode(file_get_contents(self::$targetJson));
		if($scope == 'db'){
			return json_encode($fullFile, JSON_PRETTY_PRINT);
		}
	}

	private static function wrapJsonOutput($value)
	{
		return '<pre>'.$value.'</pre>';
	}
}