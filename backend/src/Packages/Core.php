<?php

namespace Packages;

use Exception;
use Services\Database;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class Core implements HttpKernelInterface
{
    // public $alias = [];

    public function __construct(private UrlMatcher $matcher, private ControllerResolver $controllerResolver, private ArgumentResolver $argumentResolver) {
    }

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response {
        try {
            $this->matcher->getContext()->fromRequest($request);
            // Tries to match the URL against a known route pattern, and returns the corresponding route attributes in case of success
            $request->attributes->add($this->matcher->match($request->getPathInfo()));

            $controller = $this->controllerResolver->getController($request);

            if (false === $controller) {
                throw new ResourceNotFoundException(sprintf('Unable to find the controller for path "%s". The route "new" has a null controller.', $request->getPathInfo()));
            }
            
            $arguments = $this->argumentResolver->getArguments($request, $controller);

            return call_user_func_array($controller, $arguments);
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

    /* public function registerCoreModules() {
        $this->alias = [
            'db' => [Database::class]
        ];
    } */
}
