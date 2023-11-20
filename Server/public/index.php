<?php

// index
// Copyright © 2023 Joel A Mussman. All rights reserved.
//

require "../bootstrap.php";

use Src\Controllers\VerifyEssentialsController;
use Src\Factories\ServiceFactory;

// Seup to always return CORS headers so this API can work.

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];
$uriParts = explode( '/', $uri );

$routes = [
    'verify.essentials' => [
        'method' => 'GET',
        'expression' => '/^\/verifyessentials\/?$/',
        'controller_method' => 'index'
    ]
];

$routeFound = null;
foreach ($routes as $route) {
    if ($route['method'] == $requestMethod &&
        preg_match($route['expression'], $uri))
    {
        $routeFound = $route;
        break;
    }
}

if (! $routeFound) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$methodName = $route['controller_method'];

$controller = new VerifyEssentialsController();
$controller->$methodName($uriParts);