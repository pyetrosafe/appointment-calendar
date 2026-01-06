<?php

use Controllers\TaskController;
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
}, 'GET');

$routes[] = array('/task', [TaskController::class, 'index'], 'GET');
$routes[] = array('/task/{id}', [TaskController::class, 'show'], 'GET');
$routes[] = array('/task', [TaskController::class, 'store'], 'POST');
$routes[] = array('/task/{id}', [TaskController::class, 'update'], 'PUT');
$routes[] = array('/task/{id}/status', [TaskController::class, 'updateStatus'], 'PATCH');
$routes[] = array('/task/{id}', [TaskController::class, 'delete'], 'DELETE');

return $routes;
