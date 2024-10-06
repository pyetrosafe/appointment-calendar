<?php

namespace Packages;

use Composer\Autoload\ClassLoader;
use DomainException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Exception;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Controller;

class Core implements HttpKernelInterface
{

    /**
     * @var RouteCollection
     */
    protected RouteCollection $routes;

    /**
     * @var ClassLoader
     */
    protected ClassLoader $loader;

    protected Request $request;

    public function __construct() {
        $this->routes = new RouteCollection();
    }

    public function setLoader(ClassLoader $loader) {
        $this->loader = $loader;
    }

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        try {

            $this->request = $request;

            // Choice just one
            // Test this
            // $response = $this->invoke();
            // Or test this
            $response = $this->invokeController();

        } catch (ResourceNotFoundException $e) {
            // No route matched, this is a not found.
            $response = new Response('Not Found', Response::HTTP_NOT_FOUND);
        } catch (Exception $exception) {
            $response = new Response('An error occurred: ' . $exception->getMessage(), 500);
        }

        if ($response == null) {
            $response = new Response();
        }

        return $response;
    }

    protected function getAttributes(): Array {
        // Create a context using the current request
        $context = new RequestContext();
        $context->fromRequest($this->request);

        // Tell to the UrlMatcher instance how to match its routes against the requested URI by providing a context to it, using a RequestContext instance
        $matcher = new UrlMatcher($this->routes, $context);

        // Tries to match the URL against a known route pattern, and returns the corresponding route attributes in case of success
        $attributes = $matcher->match($this->request->getPathInfo());
        $this->request->attributes->add($attributes);

        return $attributes;
    }

    /**
     * @var controller like [TaskController::class, 'index']
     */
    protected function invoke(): mixed {
        //
        $controller = $this->getAttributes()['_controller'];
        $this->request->attributes->remove('_controller');
        $this->request->attributes->remove('_route');

        // Execute the callback
        if (is_array($controller)) {
            if ($this->loader && !$this->loader->findFile($controller[0]))
                throw new Exception('Class not found: '. $controller[0]);

            if ($this->loader->findFile($controller[0])) {

                // Refatorar depois para incluir
                // $this->reflection();
                // podendo passar o $request ou não como primeiro parâmetro
                // bastando "ler" o método e saber se foi construído com esse parâmetro

                $response = call_user_func_array(array(new $controller[0], $controller[1]), array($this->request, $this->request->attributes->all()));
            }
        } else {
            $response = call_user_func_array($controller, $this->request->attributes->all());
        }

        return $response;
    }

    /**
     * @var controller like TaskController::class.'::index'
     */
    protected function invokeController(): mixed {
        $this->getAttributes();

        $controllerResolver = new Controller\ControllerResolver();
        $argumentResolver = new Controller\ArgumentResolver();

        $controller = $controllerResolver->getController($this->request);
        $arguments = $argumentResolver->getArguments($this->request, $controller);

        $response = call_user_func_array($controller, $arguments);
        return $response;
    }

    // Associates an URL with a callback function
    public function map($path, $controller) {
        // Registers a Route instance into this collection
        $this->routes->add($path, new Route($path, array('_controller' => $controller)));
    }

    // source: https://stackoverflow.com/a/346789/3229228
    private function reflection($controller, $mehotd) {
        $reflection = new ReflectionMethod($controller, $mehotd);
        $parameters = $reflection->getParameters();

        $validParameters = [];

        // Variavel que era pra estar no escopo de fora
        $valuesToProcess = [];

        foreach ($parameters as $parameter) {
            if (!array_key_exists($parameter->getName(), $valuesToProcess) && !$parameter->isOptional()) {
                throw new DomainException('Cannot resolve the parameter' . $parameter->getName());
            }

            if (!array_key_exists($parameter->getName(), $valuesToProcess)) {
                continue;
            }

            $validParameters[$parameter->getName()] = $valuesToProcess[$parameter->getName()];
        }

        $reflection->invoke(...$validParameters);
    }
}
