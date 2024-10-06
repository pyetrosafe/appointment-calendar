<?php
$loader = require_once('autoload.php');

// ini_set('error_reporting', E_ERROR);

use App\Controllers\TaskController;
use Packages\Core;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Services\Database;

try {
    // Our framework is now handling itself the request
    $app = new Core();
    $app->setLoader($loader);

    $db = new Database();

    $routes = include_once('Routes/routes.php');

    foreach($routes as $r) {
        $app->map($r[0], $r[1]);
    }

    $request = Request::createFromGlobals();

    $response = $app->handle($request);
    $response->send();

} catch (Exception $execption) {
    dump("Conexão não estabelecida: " . $execption->getMessage());
}
