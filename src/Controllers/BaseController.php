<?php

namespace Controllers;

use App\Classes\Http\Response;

/**
 * @OA\Info(title="Phone book API", version="0.1")
 */

abstract class BaseController
{
	public $response;
	
	public function __construct()
	{
		$this->response = new Response();
	}
}