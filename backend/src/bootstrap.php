<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Packages\Core;
use Packages\Routes;
use Symfony\Component\HttpFoundation\Request;
use Services\Database;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

try {
    $request = Request::createFromGlobals();
    $routes = include_once('Routes/routes.php');

    $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/..');
    $dotenv->load();

    // Custom class Routes to wrap all routes
    $router = new Routes($routes);

    $context = new RequestContext();
    // Tell to the UrlMatcher instance how to match its routes against the requested URI by providing a context to it, using a RequestContext instance
    $matcher = new UrlMatcher($router->getRoutes(), $context);

    $controllerResolver = new ControllerResolver();
    $argumentResolver = new ArgumentResolver();

    // Our framework is now handling itself the request
    $app = new Core($matcher, $controllerResolver, $argumentResolver);

    $response = $app->handle($request);
    $response->send();

} catch (Exception $execption) {
    dump("Conexão não estabelecida: " . $execption->getMessage());
}
