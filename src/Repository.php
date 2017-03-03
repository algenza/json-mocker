<?php
namespace Algenza\Json\Mocker;

class Repository
{
	private $targetJson;

	public function __construct($targetJson = null){
		$this->targetJson = $targetJson;
	}

	public function getAll()
	{
		return json_decode(file_get_contents($this->targetJson));
	}

	public function getDataList($object, $params = null)
	{
		$fullFile = $this->getAll();
		$filteredData = array_filter($fullFile->{$object}, function ($obj) use ($params){
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

		return $filteredData;
	}

	public function getData($object, $id){
		$fullFile = $this->getAll();

		foreach ($fullFile->{$object} as $item) {
			if($item->id == $id){
				return $item;
			}
		}
		return false;
	}

	public function addData($object, $params){
		$fullFile = $this->getAll();
		$maxid = 1;
		if(isset($fullFile->{$object})){
			foreach ($fullFile->{$object} as $item) {
				if($item->id > $maxid){
					$maxid = $item->id;
				}
			}			
		}else{
			throw new \Exception("Scema not exist:".$object, 1);			
		}
		$newdata = new \stdClass();;
		$newdata->id = (int)$maxid+1;
		$data = json_decode($params);
		if(isset($data->id)){
			unset($data->id);
		}

		foreach ($data as $key => $value) {
			$newdata->{$key} = $value; 
		}

		$fullFile->{$object}[] = $newdata;

		if(file_put_contents($this->targetJson,json_encode($fullFile))===false){
			throw new \Exception("Add Data Failed:".$object, 1);		
		}

		return $newdata;
	}

	public function fullUpdate($object, $id, $content){

		$fullFile = $this->getAll();

		$newItem = json_decode($content);

		foreach ($fullFile->{$object} as &$item) {
			if($item->id == $id){

				$updatedItem =  new \stdClass();
				$updatedItem->id = $id;

				foreach ($newItem as $key => $value) {
					$updatedItem->{$key} = $value; 
				}

				$item = $updatedItem;

				if(file_put_contents($this->targetJson,json_encode($fullFile))===false){
					throw new \Exception("Update Data Failed:".$object.", id:".$id, 1);		
				}

				return $updatedItem;
			}
		}

		throw new \Exception("Item Not Found:".$object.", id:".$id, 1);		
		
	}

	public function partialUpdate($object, $id, $content){

		$fullFile = $this->getAll();

		$newFields = json_decode($content);

		foreach ($fullFile->{$object} as &$item) {
			if($item->id == $id){

				foreach ($newFields as $key => $value) {
					if($value==null){
						unset($item->{$key}); 
					}else{
						$item->{$key} = $value; 						
					}

				}

				if(file_put_contents($this->targetJson,json_encode($fullFile))===false){
					throw new \Exception("Update Data Failed:".$object.", id:".$id, 1);		
				}

				return $item;
			}
		}

		throw new \Exception("Item Not Found:".$object.", id:".$id, 1);		
		
	}

	public function delete($object, $id)
	{
		$fullFile = $this->getAll();
		$data = $this->getData($object, $id);
		$newData = [];
		foreach ($fullFile->{$object} as $record) {
			if($data != $record){
				$newData[]=$record;
			}
		}
		$fullFile->{$object} = $newData;
		$result = file_put_contents($this->targetJson,json_encode($fullFile));
		if($result===false){
			throw new \Exception("Update Data Failed:".$object.", id:".$id, 1);		
		}

		return true;
		return false;

	}

}
