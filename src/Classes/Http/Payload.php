<?php

namespace App\Classes\Http;


class Payload
{
	private $message;
	private $data;
	
	public function __construct($message = "", $data = [])
	{
		$this->message = $message;
		$this->data = $data;
	}
	
	public function setMessage($message = ""){
		$this->message = $message;
	}
	
	public function setData(array $data = []){
		$this->data = $data;
	}
	
	private function toArray(){
		return [
			'message' => $this->message,
			'data' => $this->data,
		];
	}
	
	public function toJSON() : string {
		return json_encode($this->toArray());
	}
	
}