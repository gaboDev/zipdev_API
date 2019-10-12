<?php

namespace Controllers;

use App\Classes\Http\Response;

abstract class BaseController
{
	public $response;
	
	public function __construct()
	{
		$this->response = new Response();
	}
}