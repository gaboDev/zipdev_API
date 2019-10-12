<?php

namespace Config;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class Bootloader
{
	private $request;
	private $requestContext;
	private $ctrlResolver;
	private $argsResolver;
	private $routesMatcher;
	
	public function __construct(RouteCollection $routes, Request $request)
	{
		$this->request = $request;
		$this->requestContext  = new RequestContext();
		$this->requestContext->fromRequest($this->request);
		$this->routesMatcher = new UrlMatcher($routes, $this->requestContext);
		$this->ctrlResolver = new ControllerResolver();
		$this->argsResolver = new ArgumentResolver();
	}
	
	public function handleRequest()
	{
		$this->routesMatcher->getContext()->fromRequest($this->request);
		
		try {
			$this->request->attributes->add($this->routesMatcher->match($this->request->getPathInfo()));
			$controller = $this->ctrlResolver->getController($this->request);
			$arguments = $this->argsResolver->getArguments($this->request, $controller);
			return call_user_func_array($controller, $arguments);
		} catch (ResourceNotFoundException $exception) {
			return new Response('Not Found', 404);
		} catch (\Exception $exception) {
			return new Response('An error occurred', 500);
		}
	}
	
}