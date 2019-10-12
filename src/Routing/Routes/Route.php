<?php

namespace Routing\Routes;


use Symfony\Component\Routing\RouteCollection;

interface Route
{
	static function getRoutes()    : RouteCollection;
}