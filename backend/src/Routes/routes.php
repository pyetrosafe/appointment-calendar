<?php

use App\Controllers\TaskController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

$routes = array();

$routes[] = array('/', function () {
    return new Response('This is the home page');
});

// $routes[] = array('/task', [TaskController::class, 'index']);
$routes[] = array('/task', TaskController::class.'::index');

// $routes[] = array('/task/{id}', [TaskController::class, 'list']);
$routes[] = array('/task/{id}', TaskController::class.'::list');

// Avoid error
$routes[] = array('/favicon.ico', function () { return null; });

return $routes;
