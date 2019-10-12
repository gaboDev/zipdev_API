<?php

namespace Routing\Routes;

use Symfony\Component\Routing\RouteCollection;
use Routing\Routes\Route as RouteInterface;
use Routing\Router;

class Phones implements RouteInterface
{
	static function getRoutes(): RouteCollection
	{
		$path = 'phones';
		$controller = "Controllers\V1\PhonesController";
		return Router::createRESTRoutes($path, $controller);
	}
}