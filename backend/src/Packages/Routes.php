<?php

namespace Packages;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Routes {

    private RouteCollection $routes;

    public function __construct(Array $routes) {
        $this->routes = new RouteCollection();

        foreach($routes as $r) {
            $this->map($r[0], $r[1]);
        }
    }

    public function getRoutes() {
        return $this->routes;
    }

    // Associates an URL with a callback function
    public function map($path, $controller) {
        // Registers a Route instance into this collection
        $this->routes->add($path, new Route($path, array('_controller' => $controller)));
    }
}
