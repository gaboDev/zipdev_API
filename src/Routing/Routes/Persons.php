<?php

namespace Routing\Routes;

use Symfony\Component\Routing\RouteCollection;
use Routing\Routes\Route as RouteInterface;
use Routing\Router;

class Persons implements RouteInterface
{
	static function getRoutes(): RouteCollection
	{
		$path = 'persons';
		$controller = "Controllers\V1\PersonsController";
		return Router::createRESTRoutes($path, $controller);
	}
}