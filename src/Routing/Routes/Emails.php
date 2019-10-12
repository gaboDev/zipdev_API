<?php

namespace Routing\Routes;

use Symfony\Component\Routing\RouteCollection;
use Routing\Routes\Route as RouteInterface;
use Routing\Router;

class Emails implements RouteInterface
{
	static function getRoutes(): RouteCollection
	{
		$path = 'emails';
		$controller = "Controllers\V1\EmailsController";
		return Router::createRESTRoutes($path, $controller);
	}
}