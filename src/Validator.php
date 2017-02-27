<?php
namespace Algenza\Json\Mocker;

use Algenza\Fjg\Validator as FjgValidator;
use Algenza\Json\Mocker\Repository;

class Validator extends FjgValidator
{
	public function isValidJson($data)
	{
		if($this->checkJsonFirstChar(substr($data,0,1)) && $this->isJson($data)){
			$obj = json_decode($data);
			$arr = (array) $obj;
			if(count($arr)>0){
				return true;
			}
		}
		return false;
	}

	public function isValidUri(Repository $repository, $uriPart, $forPost = false)
	{
		$fullFile = $repository->getAll();
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

	private function isJson($fileContent)
	{
		$result = json_decode($fileContent);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	private function checkJsonFirstChar($char)
	{
		if($char === '{'){
			return true;
		}
		return false;
	}
}