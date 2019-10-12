<?php

namespace Routing;

use Routing\Routes\Emails;
use Routing\Routes\Persons;
use Routing\Routes\Phones;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Router
{
	public static $RESTMethods = ['get', 'post', 'delete', 'put'];

	private static $Router;
	private $routes;
	
	
	public function __construct()
	{
		$this->routes = new RouteCollection();
	}
	
	private function attachRoutes(){
		$this->routes->addCollection(Persons::getRoutes());
		$this->routes->addCollection(Phones::getRoutes());
		$this->routes->addCollection(Emails::getRoutes());
		$this->routes->addPrefix('/api/v1');
	}
	
	public function getRouteCollection(): RouteCollection {
		return $this->routes;
	}
	
	
	public static function getRouter() : RouteCollection{
		if (is_null(self::$Router)){
			self::$Router = new Router();
			self::$Router->attachRoutes();
		}
		return self::$Router->getRouteCollection();
	}
	
	public static function createRESTRoutes(string $path, string $controller) : RouteCollection {
		$routeCollection = new RouteCollection();
		foreach (Router::$RESTMethods as $restMethod){
			$route  = new Route($path, ['_controller' => "$controller::$restMethod"]);
			$route->setMethods($restMethod);
			$routeCollection->add("$path/$restMethod",   $route);
		}
		
		return $routeCollection;
	}
	
	
}