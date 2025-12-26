<?php

namespace Packages;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Routes {

    private RouteCollection $routes;

    public function __construct(Array $routes) {
        $this->routes = new RouteCollection();

        foreach($routes as $r) {
            $path = $r[0];
            $controller = $r[1];
            $method = $r[2] ?? null; // GET, POST, or null

            // Use a unique name for the route, e.g., /task_POST
            $routeName = $method ? $path . '_' . $method : $path;

            $this->map($routeName, $path, $controller, $method);
        }
    }

    public function getRoutes() {
        return $this->routes;
    }

    // Associates an URL with a callback function
    public function map($name, $path, $controller, $method) {
        $methods = $method ? [$method] : []; // Route constructor expects an array of methods
        $this->routes->add($name, new Route($path, ['_controller' => $controller], methods: $methods));
    }
}
