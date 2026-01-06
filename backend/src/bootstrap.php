<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Packages\Core;
use Packages\CORS;
use Packages\Routes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use DI\ContainerBuilder;
use Packages\ContainerAwareControllerResolver;

try {
    $containerBuilder = new ContainerBuilder();
    $container = $containerBuilder->build();

    $request = Request::createFromGlobals();
    $routes = include_once('Routes/routes.php');

    $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/..');
    $dotenv->load();

    // Custom class Routes to wrap all routes
    $router = new Routes($routes);
    $context = new RequestContext();
    $context->fromRequest($request);
    // Tell to the UrlMatcher instance how to match its routes against the requested URI by providing a context to it, using a RequestContext instance
    $matcher = new UrlMatcher($router->getRoutes(), $context);

    $controllerResolver = new ContainerAwareControllerResolver($container);
    $argumentResolver = new ArgumentResolver();

    // Our framework is now handling itself the request
    $app = $container->make(Core::class, [
        'matcher' => $matcher,
        'controllerResolver' => $controllerResolver,
        'argumentResolver' => $argumentResolver]
    );

    $response = $app->handle($request);

    // Call Handler for CORS
    CORS::handle($request, $response);

    $response->send();

} catch (Exception $execption) {
    dump("Conexão não estabelecida: " . $execption->getMessage());
}
