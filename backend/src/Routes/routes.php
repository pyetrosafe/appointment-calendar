<?php

use App\Controllers\TaskController;
use Symfony\Component\HttpFoundation\Response;

$routes = array();

// Avoid error
$routes[] = array('/favicon.ico', function () { return null; });

// Controller::method can be passed 3 ways:
//   Like array [Controller::class, 'method']
//   Like string 'Namespace\Controllher::method'
//   Like string 'TaskController::class . '::index'

$routes[] = array('/', function () {
    return new Response('This is the home page');
});

$routes[] = array('/task', [TaskController::class, 'index']);

$routes[] = array('/task/{id}', [TaskController::class, 'list']);

return $routes;
