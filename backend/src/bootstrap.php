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

    // --- CORS Handling ---
    // Define a origem permitida (seu frontend)
    $allowedOrigin = 'http://localhost:3000';
    // Métodos HTTP permitidos
    $allowedMethods = 'GET, POST, PUT, DELETE, OPTIONS';
    // Cabeçalhos permitidos na requisição
    $allowedHeaders = 'Content-Type, Authorization, X-Requested-With'; // Adicione outros cabeçalhos personalizados se usar

    // Lida com a requisição OPTIONS (preflight)
    if ($request->getMethod() === 'OPTIONS') {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->headers->set('Access-Control-Allow-Headers', $allowedHeaders);
        $response->setStatusCode(204);
        $response->send();
        exit(); // Termina o script após enviar a resposta do preflight
    }

    // Para requisições reais (GET, POST, PUT, DELETE), define o cabeçalho Access-Control-Allow-Origin
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
    // CORS - Permite Content-Type
    $response->headers->set('Access-Control-Allow-Headers', $allowedHeaders);
    $response->send();

} catch (Exception $execption) {
    dump("Conexão não estabelecida: " . $execption->getMessage());
}
