<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Config\Bootloader;
use Routing\Router;
use Symfony\Component\HttpFoundation\Request;

// Load env variables
$dotenv = \Dotenv\Dotenv::create(__DIR__."/../");
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

// Create app instance
$app      = new Bootloader(Router::getRouter(), Request::createFromGlobals());
$response = $app->handleRequest();
$response->send();