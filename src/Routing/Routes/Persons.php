<?php

namespace Routing\Routes;

use Symfony\Component\Routing\RouteCollection;
use Routing\Routes\Route as RouteInterface;
use Routing\Router;
use Symfony\Component\Routing\Route as SymfonyRoute;

class Persons implements RouteInterface
{
	static function getRoutes(): RouteCollection
	{
		$path = 'persons';
		$controller = "Controllers\V1\PersonsController";
		$RESTRutes = Router::createRESTRoutes($path, $controller);
		$RESTRutes->add('persons_getAllRelatedData', new SymfonyRoute("$path/getAllRelatedData", ['_controller' => "$controller::getAllRelatedData"]));
		return $RESTRutes;
	}
}